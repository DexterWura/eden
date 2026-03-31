<?php

namespace App\Http\Controllers\Eden;

use App\Models\PaymentGateway;
use App\Support\Seo\EdenSeo;
use App\Models\ProPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PricingController extends EdenController
{
    const PRO_PRICE = 9.99;
    const PRO_CURRENCY = 'USD';

    public function index()
    {
        $isPro = auth()->check() && auth()->user()->isPro();
        $gateways = PaymentGateway::enabled()->get();

        return $this->page('pricing', 'Pricing', null, compact('isPro', 'gateways'), EdenSeo::forStaticPath('/pricing'));
    }

    public function checkout(Request $request)
    {
        $request->validate(['gateway' => 'required|string']);

        $user = auth()->user();
        if ($user->isPro()) {
            return redirect(url('/pricing'))->with('error', 'You already have Pro access.');
        }

        $gateway = PaymentGateway::where('alias', $request->gateway)->where('enabled', true)->first();
        if (!$gateway) {
            return redirect(url('/pricing'))->with('error', 'Selected payment gateway is not available.');
        }

        $trx = 'PRO' . strtoupper(Str::random(16));

        $payment = ProPayment::create([
            'user_id' => $user->id,
            'gateway' => $gateway->alias,
            'trx' => $trx,
            'amount' => self::PRO_PRICE,
            'currency' => self::PRO_CURRENCY,
            'status' => 'pending',
        ]);

        return match ($gateway->alias) {
            'paypal' => $this->initiatePaypal($payment, $gateway),
            'paynow' => $this->initiatePaynow($payment, $gateway),
            default => redirect(url('/pricing'))->with('error', 'Unsupported gateway.'),
        };
    }

    private function initiatePaypal(ProPayment $payment, PaymentGateway $gateway)
    {
        $clientId = $gateway->param('client_id');
        $secret = $gateway->param('secret');
        $mode = $gateway->param('mode', 'sandbox');

        $baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $tokenResponse = $this->paypalRequest('POST', $baseUrl . '/v1/oauth2/token', [
            'headers' => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
            'auth' => [$clientId, $secret],
            'body' => 'grant_type=client_credentials',
        ]);

        if (!$tokenResponse || empty($tokenResponse['access_token'])) {
            return redirect(url('/pricing'))->with('error', 'Could not connect to PayPal. Please try again.');
        }

        $accessToken = $tokenResponse['access_token'];

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $payment->trx,
                'description' => 'Eden Pro — Lifetime Access',
                'amount' => [
                    'currency_code' => $payment->currency,
                    'value' => number_format($payment->amount, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'brand_name' => function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden',
                'return_url' => url('/checkout/paypal/return?trx=' . $payment->trx),
                'cancel_url' => url('/checkout/paypal/cancel?trx=' . $payment->trx),
                'user_action' => 'PAY_NOW',
            ],
        ];

        $orderResponse = $this->paypalRequest('POST', $baseUrl . '/v2/checkout/orders', [
            'headers' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            'body' => json_encode($orderData),
        ]);

        if (!$orderResponse || empty($orderResponse['id'])) {
            return redirect(url('/pricing'))->with('error', 'Could not create PayPal order. Please try again.');
        }

        $payment->update(['gateway_response' => ['order_id' => $orderResponse['id']]]);

        $approveLink = collect($orderResponse['links'] ?? [])->firstWhere('rel', 'approve');
        if (!$approveLink) {
            return redirect(url('/pricing'))->with('error', 'Could not get PayPal approval link.');
        }

        return redirect($approveLink['href']);
    }

    public function paypalReturn(Request $request)
    {
        $payment = ProPayment::where('trx', $request->query('trx'))->where('status', 'pending')->first();
        if (!$payment) {
            return redirect(url('/pricing'))->with('error', 'Payment not found.');
        }

        $gateway = PaymentGateway::where('alias', 'paypal')->where('enabled', true)->first();
        if (!$gateway) {
            return redirect(url('/pricing'))->with('error', 'PayPal gateway is not configured.');
        }

        $mode = $gateway->param('mode', 'sandbox');
        $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $tokenResponse = $this->paypalRequest('POST', $baseUrl . '/v1/oauth2/token', [
            'headers' => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
            'auth' => [$gateway->param('client_id'), $gateway->param('secret')],
            'body' => 'grant_type=client_credentials',
        ]);

        if (!$tokenResponse || empty($tokenResponse['access_token'])) {
            return redirect(url('/pricing'))->with('error', 'Could not verify with PayPal.');
        }

        $orderId = $payment->gateway_response['order_id'] ?? null;
        if (!$orderId) {
            return redirect(url('/pricing'))->with('error', 'Missing order reference.');
        }

        $captureResponse = $this->paypalRequest('POST', $baseUrl . '/v2/checkout/orders/' . $orderId . '/capture', [
            'headers' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $tokenResponse['access_token'],
            ],
            'body' => '{}',
        ]);

        if ($captureResponse && ($captureResponse['status'] ?? '') === 'COMPLETED') {
            $this->activatePro($payment, $captureResponse);
            return redirect(url('/pricing'))->with('success', 'Welcome to Pro! Your lifetime access is now active.');
        }

        return redirect(url('/pricing'))->with('error', 'Payment could not be captured. Please contact support.');
    }

    public function paypalCancel(Request $request)
    {
        $payment = ProPayment::where('trx', $request->query('trx'))->where('status', 'pending')->first();
        if ($payment) {
            $payment->update(['status' => 'cancelled']);
        }
        return redirect(url('/pricing'))->with('error', 'Payment was cancelled.');
    }

    private function initiatePaynow(ProPayment $payment, PaymentGateway $gateway)
    {
        require_once app_path('Http/Controllers/Gateway/Paynow/autoloader.php');

        $integrationId = $gateway->param('integration_id');
        $integrationKey = $gateway->param('integration_key');

        $returnUrl = url('/checkout/paynow/return?trx=' . $payment->trx);
        $resultUrl = url('/checkout/paynow/callback');

        $paynow = new \Paynow\Payments\Paynow($integrationId, $integrationKey, $returnUrl, $resultUrl);

        $paynowPayment = $paynow->createPayment($payment->trx, auth()->user()->email);
        $paynowPayment->add('Eden Pro — Lifetime Access', $payment->amount);

        try {
            $response = $paynow->send($paynowPayment);
            if (!$response->success()) {
                return redirect(url('/pricing'))->with('error', 'Paynow error: ' . $response->error());
            }

            $payment->update([
                'gateway_response' => ['poll_url' => $response->pollUrl()],
            ]);

            return redirect($response->redirectUrl());
        } catch (\Exception $e) {
            return redirect(url('/pricing'))->with('error', 'Paynow error: ' . $e->getMessage());
        }
    }

    public function paynowReturn(Request $request)
    {
        $payment = ProPayment::where('trx', $request->query('trx'))->where('status', 'pending')->first();
        if (!$payment) {
            return redirect(url('/pricing'))->with('error', 'Payment not found.');
        }

        $gateway = PaymentGateway::where('alias', 'paynow')->where('enabled', true)->first();
        if (!$gateway) {
            return redirect(url('/pricing'))->with('error', 'Paynow gateway is not configured.');
        }

        require_once app_path('Http/Controllers/Gateway/Paynow/autoloader.php');

        $paynow = new \Paynow\Payments\Paynow(
            $gateway->param('integration_id'),
            $gateway->param('integration_key'),
            url('/checkout/paynow/return?trx=' . $payment->trx),
            url('/checkout/paynow/callback')
        );

        $pollUrl = $payment->gateway_response['poll_url'] ?? null;
        if (!$pollUrl) {
            return redirect(url('/pricing'))->with('error', 'Missing poll reference.');
        }

        try {
            $status = $paynow->pollTransaction($pollUrl);
            if ($status->paid()) {
                $this->activatePro($payment, [
                    'paynow_reference' => $status->paynowReference(),
                    'status' => $status->status(),
                    'amount' => $status->amount(),
                ]);
                return redirect(url('/pricing'))->with('success', 'Welcome to Pro! Your lifetime access is now active.');
            }
        } catch (\Exception $e) {
            // fall through
        }

        return redirect(url('/pricing'))->with('error', 'Payment not yet confirmed. If you completed payment, it may take a moment—refresh this page shortly.');
    }

    public function paynowCallback(Request $request)
    {
        $trx = $request->input('reference') ?? $request->input('trx');
        $payment = ProPayment::where('trx', $trx)->where('status', 'pending')->first();
        if (!$payment) {
            return response('Not found', 404);
        }

        $gateway = PaymentGateway::where('alias', 'paynow')->where('enabled', true)->first();
        if (!$gateway) {
            return response('Gateway not configured', 400);
        }

        require_once app_path('Http/Controllers/Gateway/Paynow/autoloader.php');

        $paynow = new \Paynow\Payments\Paynow(
            $gateway->param('integration_id'),
            $gateway->param('integration_key'),
            url('/checkout/paynow/return?trx=' . $payment->trx),
            url('/checkout/paynow/callback')
        );

        try {
            $status = $paynow->processStatusUpdate();
            if ($status->paid()) {
                $this->activatePro($payment, [
                    'paynow_reference' => $status->paynowReference(),
                    'status' => $status->status(),
                    'amount' => $status->amount(),
                ]);
                return response('OK', 200);
            }
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 400);
        }

        return response('Not paid', 400);
    }

    private function activatePro(ProPayment $payment, array $responseData): void
    {
        $payment->update([
            'status' => 'paid',
            'gateway_response' => array_merge($payment->gateway_response ?? [], $responseData),
        ]);

        $user = $payment->user;
        $user->is_pro = true;
        $user->pro_since = now();
        $user->save();
    }

    private function paypalRequest(string $method, string $url, array $options): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = $options['headers'] ?? [];
        if (!empty($options['auth'])) {
            curl_setopt($ch, CURLOPT_USERPWD, $options['auth'][0] . ':' . $options['auth'][1]);
        }
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body'] ?? '');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }
}
