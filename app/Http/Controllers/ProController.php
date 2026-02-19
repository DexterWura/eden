<?php

namespace App\Http\Controllers;

use App\Models\FeaturePayment;
use App\Services\PaynowService;
use App\Services\ProFeatureService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProController extends Controller
{
    public function index(ProFeatureService $pro): View
    {
        $features = $pro->getProFeatures();
        $gateways = $pro->getGateways();
        $prices = [];
        $currencies = $pro->getCurrencies();
        foreach (array_keys($features) as $key) {
            $prices[$key] = [
                'USD' => $pro->getPrice($key, 'USD'),
                'ZWL' => $pro->getPrice($key, 'ZWL'),
            ];
        }

        return view('pro.index', [
            'features' => $features,
            'gateways' => $gateways,
            'prices' => $prices,
            'currencies' => $currencies,
        ]);
    }

    public function checkout(Request $request, ProFeatureService $pro, PaynowService $paynow): RedirectResponse
    {
        $proFeatureKeys = array_keys($pro->getProFeatures());
        if (empty($proFeatureKeys)) {
            return redirect()->route('pro.index')->with('error', 'No pro features are currently available for purchase.');
        }
        $validated = $request->validate([
            'feature_key' => 'required|string|in:' . implode(',', $proFeatureKeys),
            'gateway' => 'required|string|in:' . implode(',', array_keys($pro->getGateways())),
            'currency' => 'nullable|string|in:USD,ZWL',
        ]);

        $featureKey = $validated['feature_key'];
        $gateway = $validated['gateway'];
        $currency = $validated['currency'] ?? 'USD';

        if ($request->user()->hasProFeature($featureKey)) {
            return redirect()->route('pro.index')->with('info', 'You already have access to this feature.');
        }

        $amount = $pro->getPrice($featureKey, $currency);
        if ($amount <= 0) {
            return redirect()->route('pro.index')->with('error', 'This feature is not available for purchase.');
        }

        $payment = FeaturePayment::create([
            'user_id' => $request->user()->id,
            'feature_key' => $featureKey,
            'amount' => $amount,
            'currency' => $currency,
            'gateway' => $gateway,
            'status' => FeaturePayment::STATUS_PENDING,
        ]);

        if ($gateway === 'paynow') {
            if (! $paynow->isConfigured()) {
                $payment->update(['status' => FeaturePayment::STATUS_FAILED]);
                return redirect()->route('pro.index')->with('error', 'PayNow is not configured. Please try later.');
            }
            $returnUrl = route('payment.return', ['payment' => $payment->id]);
            $resultUrl = route('payment.paynow.result');
            $result = $paynow->createPayment($payment, $returnUrl, $resultUrl);
            if (! $result['success']) {
                $payment->update(['status' => FeaturePayment::STATUS_FAILED]);
                return redirect()->route('pro.index')->with('error', $result['error'] ?? 'Could not start payment.');
            }
            $payment->update([
                'gateway_reference' => $result['poll_url'],
                'gateway_response' => json_encode(['poll_url' => $result['poll_url']]),
            ]);

            if (! empty($result['redirect_link'])) {
                return redirect()->away($result['redirect_link']);
            }
            $payment->update(['status' => FeaturePayment::STATUS_FAILED]);

            return redirect()->route('pro.index')->with('error', 'Payment redirect was not from a trusted source. Please try again.');
        }

        if ($gateway === 'paypal') {
            return redirect()->route('payment.return', ['payment' => $payment->id])
                ->with('info', 'PayPal checkout is not yet implemented. Your payment is pending.');
        }

        return redirect()->route('pro.index')->with('error', 'Unknown gateway.');
    }

    public function return(Request $request, FeaturePayment $payment): View|RedirectResponse
    {
        if ($payment->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($payment->isPaid()) {
            return view('payment.return', ['payment' => $payment, 'success' => true]);
        }

        if ($payment->gateway === 'paynow' && $payment->gateway_response) {
            $data = json_decode($payment->gateway_response, true);
            $pollUrl = $data['poll_url'] ?? null;
            if ($pollUrl) {
                $status = app(PaynowService::class)->pollTransaction($pollUrl);
                if ($status && $status->paid()) {
                    $payment->update([
                        'status' => FeaturePayment::STATUS_PAID,
                        'paid_at' => now(),
                    ]);

                    return view('payment.return', ['payment' => $payment, 'success' => true]);
                }
            }
        }

        return view('payment.return', ['payment' => $payment, 'success' => false]);
    }
}
