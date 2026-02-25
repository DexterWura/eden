<?php

namespace App\Services;

use App\Models\FeaturePayment;
use Paynow\Payments\Paynow;
use Paynow\Payments\StatusResponse;

class PaynowService
{
    public function __construct(
        protected SettingService $settings
    ) {}

    public function isConfigured(): bool
    {
        $id = $this->settings->get('paynow_integration_id');
        $key = $this->settings->get('paynow_integration_key');

        return ! empty(trim((string) $id)) && ! empty(trim((string) $key));
    }

    public function createPayment(FeaturePayment $payment, string $returnUrl, string $resultUrl): array
    {
        $paynow = $this->newPaynow($returnUrl, $resultUrl);
        $ref = (string) $payment->id;
        $builder = $paynow->createPayment($ref, $payment->user->email);
        $builder->add($this->paymentDescription($payment), (float) $payment->amount);
        $response = $paynow->send($builder);

        $redirectLink = $response->success() ? $response->redirectLink() : null;
        $safeRedirect = $redirectLink ? $this->getSafeRedirectUrl($redirectLink) : null;

        return [
            'success' => $response->success(),
            'redirect_link' => $safeRedirect,
            'poll_url' => $response->success() ? $response->pollUrl() : null,
            'error' => $response->success() ? null : 'Payment initiation failed',
        ];
    }

    /** Return URL only if host is in allowlist; otherwise null to prevent open redirect. */
    public function getSafeRedirectUrl(string $url): ?string
    {
        $hosts = config('services.paynow.allowed_redirect_hosts', ['paynow.co.zw', 'www.paynow.co.zw']);
        $parsed = parse_url($url);
        $host = isset($parsed['host']) ? strtolower($parsed['host']) : '';
        if ($host === '' || ! in_array($host, $hosts, true)) {
            return null;
        }

        return $url;
    }

    public function pollTransaction(string $pollUrl): ?StatusResponse
    {
        if (! $this->isConfigured()) {
            return null;
        }
        try {
            $paynow = $this->newPaynow('', '');

            return $paynow->pollTransaction($pollUrl);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function processStatusUpdate(): ?StatusResponse
    {
        $id = $this->settings->get('paynow_integration_id');
        $key = $this->settings->get('paynow_integration_key');
        if (empty($id) || empty($key)) {
            return null;
        }
        $paynow = new Paynow($id, $key, '', '');
        if (! request()->has('hash')) {
            return null;
        }
        try {
            return $paynow->processStatusUpdate();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function newPaynow(string $returnUrl, string $resultUrl): Paynow
    {
        $id = $this->settings->get('paynow_integration_id');
        $key = $this->settings->get('paynow_integration_key');

        return new Paynow($id, $key, $returnUrl, $resultUrl);
    }

    protected function paymentDescription(FeaturePayment $payment): string
    {
        $features = config('pro_features.features', []);
        $name = $features[$payment->feature_key]['name'] ?? $payment->feature_key;

        return $name . ' - Pro feature';
    }
}
