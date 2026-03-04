<?php

namespace App\Services\Revenue;

use App\Models\Startup;
use App\Models\StartupRevenueEvent;
use App\Models\StartupRevenueIntegration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevenueSyncService
{
    public function sync(StartupRevenueIntegration $integration): void
    {
        $startup = $integration->startup;
        if (! $startup->isActive()) {
            return;
        }

        $sync = match ($integration->gateway) {
            StartupRevenueIntegration::GATEWAY_STRIPE => new StripeRevenueSync(),
            StartupRevenueIntegration::GATEWAY_POLAR => new PolarRevenueSync(),
            StartupRevenueIntegration::GATEWAY_LEMONSQUEEZY => new LemonSqueezyRevenueSync(),
            default => null,
        };

        if (! $sync) {
            return;
        }

        try {
            $results = $sync->fetchRevenue($integration);
            $this->recordEvents($startup, $results);
            $integration->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Revenue sync failed', [
                'integration' => $integration->id,
                'gateway' => $integration->gateway,
                'error' => $e->getMessage(),
            ]);
            $integration->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'error',
                'settings' => array_merge($integration->settings ?? [], ['last_error' => $e->getMessage()]),
            ]);
        }
    }

    /**
     * @param  array<int, array{amount: float, currency: string, external_id: string}>  $results
     */
    private function recordEvents(Startup $startup, array $results): void
    {
        foreach ($results as $row) {
            $externalId = $row['external_id'] ?? null;
            if (! $externalId) {
                continue;
            }
            $exists = StartupRevenueEvent::where('startup_id', $startup->id)
                ->where('external_id', $externalId)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::transaction(function () use ($startup, $row) {
                StartupRevenueEvent::create([
                    'startup_id' => $startup->id,
                    'amount' => $row['amount'],
                    'currency' => strtoupper($row['currency'] ?? 'USD'),
                    'external_id' => $row['external_id'],
                ]);
                $startup->increment('revenue', $row['amount']);
            });
        }
    }
}
