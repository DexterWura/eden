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

        return [
            'success' => $response->success(),
            'redirect_link' => $response->success() ? $response->redirectLink() : null,
            'poll_url' => $response->success() ? $response->pollUrl() : null,
            'error' => $response->success() ? null : 'Payment initiation failed',
        ];
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
