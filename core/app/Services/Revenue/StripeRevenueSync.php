<?php

namespace App\Services\Revenue;

use App\Models\StartupRevenueIntegration;

class StripeRevenueSync
{
    /**
     * @return array<int, array{amount: float, currency: string, external_id: string}>
     */
    public function fetchRevenue(StartupRevenueIntegration $integration): array
    {
        $apiKey = $integration->getApiKey();
        if (! $apiKey) {
            return [];
        }

        \Stripe\Stripe::setApiKey($apiKey);

        $since = $integration->last_synced_at
            ? $integration->last_synced_at->getTimestamp()
            : strtotime('-2 years');

        $results = [];

        $charges = \Stripe\Charge::all([
            'created' => ['gte' => $since],
            'limit' => 100,
        ]);

        foreach ($charges->autoPagingIterator() as $charge) {
            if ($charge->status !== 'succeeded' || $charge->refunded) {
                continue;
            }
            $amount = $charge->amount / 100;
            $currency = strtolower($charge->currency ?? 'usd');
            $results[] = [
                'amount' => $amount,
                'currency' => $currency,
                'external_id' => 'ch_' . $charge->id,
            ];
        }

        return $results;
    }
}
