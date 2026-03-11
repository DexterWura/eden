<?php

namespace App\Http\Controllers\Eden;

use App\Models\AdSpot;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AdController extends EdenController
{
    private const BLOG_PLACEMENT = 'blog_banner_1';
    private const AD_PRICE = 2.00;
    private const AD_CURRENCY = 'USD';
    private const WIDTH = 728;
    private const HEIGHT = 90;

    public function showBlogAdForm(): Response
    {
        $gateways = PaymentGateway::enabled()->get();

        return $this->page(
            'advertise-blog',
            'Advertise on the blog',
            null,
            [
                'price' => self::AD_PRICE,
                'currency' => self::AD_CURRENCY,
                'gateways' => $gateways,
                'width' => self::WIDTH,
                'height' => self::HEIGHT,
            ]
        );
    }

    public function createBlogAd(Request $request): RedirectResponse
    {
        $request->validate([
            'contact_email' => 'required|email',
            'target_url' => 'required|url',
            'gateway' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        $gateway = PaymentGateway::where('alias', $request->input('gateway'))
            ->where('enabled', true)
            ->first();

        if (! $gateway) {
            return redirect()->back()->withInput()->with('error', 'Selected payment gateway is not available.');
        }

        $image = $request->file('image');
        $size = @getimagesize($image->getRealPath());
        if (! $size || $size[0] !== self::WIDTH || $size[1] !== self::HEIGHT) {
            return redirect()
                ->back()
                ->withInput($request->except('image'))
                ->with('error', "Ad banner must be exactly " . self::WIDTH . "x" . self::HEIGHT . " pixels.");
        }

        $path = $image->storePublicly('uploads/ads/blog', ['disk' => 'public']);

        $paymentReference = 'AD' . strtoupper(Str::random(16));

        $ad = AdSpot::create([
            'placement' => self::BLOG_PLACEMENT,
            'image_path' => $path,
            'target_url' => $request->input('target_url'),
            'status' => AdSpot::STATUS_PENDING,
            'contact_email' => $request->input('contact_email'),
            'payment_reference' => $paymentReference,
            'gateway' => $gateway->alias,
        ]);

        return match ($gateway->alias) {
            'paypal' => $this->initiatePaypal($ad, $gateway),
            'paynow' => $this->initiatePaynow($ad, $gateway),
            default => redirect()->back()->with('error', 'Unsupported payment gateway.'),
        };
    }

    public function paypalReturn(Request $request): RedirectResponse
    {
        $trx = $request->query('trx');

        $ad = AdSpot::where('payment_reference', $trx)
            ->where('status', AdSpot::STATUS_PENDING)
            ->first();

        if (! $ad) {
            return redirect(url('/advertise/blog'))->with('error', 'Ad purchase not found or already processed.');
        }

        $gateway = PaymentGateway::where('alias', 'paypal')->where('enabled', true)->first();
        if (! $gateway) {
            return redirect(url('/advertise/blog'))->with('error', 'PayPal gateway is not configured.');
        }

        $mode = $gateway->param('mode', 'sandbox');
        $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $tokenResponse = $this->paypalRequest('POST', $baseUrl . '/v1/oauth2/token', [
            'headers' => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
            'auth' => [$gateway->param('client_id'), $gateway->param('secret')],
            'body' => 'grant_type=client_credentials',
        ]);

        if (! $tokenResponse || empty($tokenResponse['access_token'])) {
            return redirect(url('/advertise/blog'))->with('error', 'Could not verify with PayPal.');
        }

        $orderId = $ad->payment_reference;

        $captureResponse = $this->paypalRequest('POST', $baseUrl . '/v2/checkout/orders/' . $orderId . '/capture', [
            'headers' => [
                'Content-Type: application/json',
                'Authorization' => 'Bearer ' . $tokenResponse['access_token'],
            ],
            'body' => '{}',
        ]);

        if ($captureResponse && ($captureResponse['status'] ?? '') === 'COMPLETED') {
            $this->activateAd($ad);
            return redirect(url('/blog'))->with('success', 'Your ad is now live on the blog for one month.');
        }

        return redirect(url('/advertise/blog'))->with('error', 'Payment could not be captured. Please contact support or try again.');
    }

    public function paypalCancel(Request $request): RedirectResponse
    {
        $trx = $request->query('trx');

        $ad = AdSpot::where('payment_reference', $trx)
            ->where('status', AdSpot::STATUS_PENDING)
            ->first();

        if ($ad) {
            $ad->status = AdSpot::STATUS_EXPIRED;
            $ad->save();
        }

        return redirect(url('/advertise/blog'))->with('error', 'Payment was cancelled.');
    }

    public function paynowReturn(Request $request): RedirectResponse
    {
        $trx = $request->query('trx');

        $ad = AdSpot::where('payment_reference', $trx)
            ->where('status', AdSpot::STATUS_PENDING)
            ->first();

        if (! $ad) {
            return redirect(url('/advertise/blog'))->with('error', 'Ad purchase not found.');
        }

        $gateway = PaymentGateway::where('alias', 'paynow')->where('enabled', true)->first();
        if (! $gateway) {
            return redirect(url('/advertise/blog'))->with('error', 'Paynow gateway is not configured.');
        }

        require_once app_path('Http/Controllers/Gateway/Paynow/autoloader.php');

        $paynow = new \Paynow\Payments\Paynow(
            $gateway->param('integration_id'),
            $gateway->param('integration_key'),
            url('/advertise/blog/paynow/return?trx=' . $ad->payment_reference),
            url('/advertise/blog/paynow/callback')
        );

        $pollUrl = $ad->payment_reference ? ($ad->payment_reference) : null;
        if (! $pollUrl) {
            return redirect(url('/advertise/blog'))->with('error', 'Missing payment reference.');
        }

        try {
            $status = $paynow->pollTransaction($pollUrl);
            if ($status->paid()) {
                $this->activateAd($ad);
                return redirect(url('/blog'))->with('success', 'Your ad is now live on the blog for one month.');
            }
        } catch (\Exception $e) {
            // fall through
        }

        return redirect(url('/advertise/blog'))->with('error', 'Payment not yet confirmed. If you completed payment, it may take a moment—refresh this page shortly.');
    }

    public function paynowCallback(Request $request): Response
    {
        require_once app_path('Http/Controllers/Gateway/Paynow/autoloader.php');

        $trx = $request->input('reference') ?? $request->input('trx');

        $ad = AdSpot::where('payment_reference', $trx)
            ->where('status', AdSpot::STATUS_PENDING)
            ->first();

        if (! $ad) {
            return response('Not found', 404);
        }

        $gateway = PaymentGateway::where('alias', 'paynow')->where('enabled', true)->first();
        if (! $gateway) {
            return response('Gateway not configured', 400);
        }

        $paynow = new \Paynow\Payments\Paynow(
            $gateway->param('integration_id'),
            $gateway->param('integration_key'),
            url('/advertise/blog/paynow/return?trx=' . $ad->payment_reference),
            url('/advertise/blog/paynow/callback')
        );

        try {
            $status = $paynow->processStatusUpdate();
            if ($status->paid()) {
                $this->activateAd($ad);
                return response('OK', 200);
            }
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 400);
        }

        return response('Not paid', 400);
    }

    private function activateAd(AdSpot $ad): void
    {
        $ad->status = AdSpot::STATUS_ACTIVE;
        $ad->starts_at = now();
        $ad->ends_at = now()->addMonth();
        $ad->save();
    }

    private function initiatePaypal(AdSpot $ad, PaymentGateway $gateway): RedirectResponse
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

        if (! $tokenResponse || empty($tokenResponse['access_token'])) {
            return redirect(url('/advertise/blog'))->with('error', 'Could not connect to PayPal. Please try again.');
        }

        $accessToken = $tokenResponse['access_token'];

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $ad->payment_reference,
                'description' => 'Blog ad spot — 1 month',
                'amount' => [
                    'currency_code' => self::AD_CURRENCY,
                    'value' => number_format(self::AD_PRICE, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'brand_name' => function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden',
                'return_url' => url('/advertise/blog/paypal/return?trx=' . $ad->payment_reference),
                'cancel_url' => url('/advertise/blog/paypal/cancel?trx=' . $ad->payment_reference),
                'user_action' => 'PAY_NOW',
            ],
        ];

        $orderResponse = $this->paypalRequest('POST', $baseUrl . '/v2/checkout/orders', [
            'headers' => [
                'Content-Type: application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'body' => json_encode($orderData),
        ]);

        if (! $orderResponse || empty($orderResponse['id'])) {
            return redirect(url('/advertise/blog'))->with('error', 'Could not create PayPal order. Please try again.');
        }

        $ad->payment_reference = $orderResponse['id'];
        $ad->save();

        $approveLink = collect($orderResponse['links'] ?? [])->firstWhere('rel', 'approve');
        if (! $approveLink) {
            return redirect(url('/advertise/blog'))->with('error', 'Could not get PayPal approval link.');
        }

        return redirect($approveLink['href']);
    }

    private function initiatePaynow(AdSpot $ad, PaymentGateway $gateway): RedirectResponse
    {
        require_once app_path('Http/Controllers/Gateway/Paynow/autoloader.php');

        $integrationId = $gateway->param('integration_id');
        $integrationKey = $gateway->param('integration_key');

        $returnUrl = url('/advertise/blog/paynow/return?trx=' . $ad->payment_reference);
        $resultUrl = url('/advertise/blog/paynow/callback');

        $paynow = new \Paynow\Payments\Paynow($integrationId, $integrationKey, $returnUrl, $resultUrl);

        $paynowPayment = $paynow->createPayment($ad->payment_reference, $ad->contact_email ?? 'guest@example.com');
        $paynowPayment->add('Blog ad spot — 1 month', self::AD_PRICE);

        try {
            $response = $paynow->send($paynowPayment);
            if (! $response->success()) {
                return redirect(url('/advertise/blog'))->with('error', 'Paynow error: ' . $response->error());
            }

            $ad->payment_reference = $response->pollUrl();
            $ad->save();

            return redirect($response->redirectUrl());
        } catch (\Exception $e) {
            return redirect(url('/advertise/blog'))->with('error', 'Paynow error: ' . $e->getMessage());
        }
    }

    private function paypalRequest(string $method, string $url, array $options): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = $options['headers'] ?? [];
        if (! empty($options['auth'])) {
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

