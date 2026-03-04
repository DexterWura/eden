<?php

namespace App\Console\Commands;

use App\Models\StartupRevenueIntegration;
use App\Services\Revenue\RevenueSyncService;
use Illuminate\Console\Command;

class SyncRevenueIntegrationsCommand extends Command
{
    protected $signature = 'revenue:sync {--startup= : Sync only for this startup ID}';

    protected $description = 'Sync revenue from connected gateways (Stripe, Polar, Lemon Squeezy)';

    public function handle(RevenueSyncService $syncService): int
    {
        $query = StartupRevenueIntegration::query()->with('startup');
        $startupId = $this->option('startup');
        if ($startupId) {
            $query->where('startup_id', $startupId);
        }

        $integrations = $query->get();
        $this->info('Syncing ' . $integrations->count() . ' integration(s)...');

        foreach ($integrations as $integration) {
            $syncService->sync($integration);
            $this->line("  Synced {$integration->gateway} for startup {$integration->startup->name}");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
