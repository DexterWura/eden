<?php

namespace App\Services\Revenue;

use App\Models\StartupRevenueIntegration;
use Illuminate\Support\Facades\Http;

class LemonSqueezyRevenueSync
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

        $results = [];
        $page = 1;

        do {
            $response = Http::withToken($apiKey)
                ->accept('application/vnd.api+json')
                ->get('https://api.lemonsqueezy.com/v1/orders', [
                    'page' => ['number' => $page, 'size' => 100],
                    'sort' => '-created_at',
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Lemon Squeezy API error: ' . $response->body());
            }

            $data = $response->json();
            $orders = $data['data'] ?? [];

            foreach ($orders as $order) {
                $attrs = $order['attributes'] ?? [];
                if (($attrs['status'] ?? '') !== 'paid' || ($attrs['refunded'] ?? false)) {
                    continue;
                }
                $totalCents = (int) ($attrs['total'] ?? $attrs['total_usd'] ?? 0);
                $amount = $totalCents / 100;
                $currency = strtolower($attrs['currency'] ?? 'usd');
                $id = $order['id'] ?? $attrs['identifier'] ?? null;
                if ($id && $amount > 0) {
                    $createdAt = $attrs['created_at'] ?? null;
                    if ($integration->last_synced_at && $createdAt) {
                        if (strtotime($createdAt) <= $integration->last_synced_at->getTimestamp()) {
                            continue;
                        }
                    }
                    $results[] = [
                        'amount' => $amount,
                        'currency' => $currency,
                        'external_id' => 'ls_' . $id,
                    ];
                }
            }

            $meta = $data['meta']['page'] ?? [];
            $lastPage = $meta['lastPage'] ?? 1;
            $page++;
        } while ($page <= $lastPage);

        return $results;
    }
}
