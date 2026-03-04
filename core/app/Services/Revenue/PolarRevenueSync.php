<?php

namespace App\Services\Revenue;

use App\Models\StartupRevenueIntegration;
use Illuminate\Support\Facades\Http;

class PolarRevenueSync
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

        $since = $integration->last_synced_at
            ? $integration->last_synced_at->format('c')
            : now()->subYears(2)->format('c');

        $results = [];
        $url = 'https://api.polar.sh/v1/orders';

        do {
            $response = Http::withToken($apiKey)
                ->accept('application/json')
                ->get($url, [
                    'created_after' => $since,
                    'limit' => 100,
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Polar API error: ' . $response->body());
            }

            $data = $response->json();
            $items = $data['items'] ?? $data['data'] ?? [];

            foreach ($items as $order) {
                $status = $order['status'] ?? null;
                $paid = $order['paid'] ?? false;
                if ($status !== 'succeeded' && ! $paid) {
                    continue;
                }
                $amountCents = $order['total_amount'] ?? $order['amount'] ?? 0;
                $refunded = $order['refunded_amount'] ?? 0;
                $amount = max(0, ($amountCents - $refunded) / 100);
                $currency = strtolower($order['currency'] ?? 'usd');
                $id = $order['id'] ?? null;
                if ($id && $amount > 0) {
                    $results[] = [
                        'amount' => $amount,
                        'currency' => $currency,
                        'external_id' => 'polar_' . $id,
                    ];
                }
            }

            $url = $data['pagination']['next'] ?? null;
        } while ($url);

        return $results;
    }
}
