<?php

namespace App\Console\Commands;

use App\Models\Startup;
use App\Services\StartupWebsiteHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStartupWebsites extends Command
{
    protected $signature = 'startups:check-websites
                            {--force : Check all startups with a website, ignoring the check interval}';

    protected $description = 'Ping startup websites; mark dormant after consecutive failures; delete if dormant too long';

    public function __construct(
        private StartupWebsiteHealthService $healthService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = $this->option('force');
        $failureThreshold = StartupWebsiteHealthService::CONSECUTIVE_FAILURES_BEFORE_DORMANT;
        $intervalDays = StartupWebsiteHealthService::CHECK_INTERVAL_DAYS;

        $this->info("Startup website check started (interval: {$intervalDays} days, dormant after {$failureThreshold} consecutive failures).");

        $markedDormant = $this->checkAndUpdateStartups($force);
        $reactivated = $this->reactivatedCount;
        $deleted = $this->deleteDormantStartups();

        $message = sprintf(
            '[%s] Marked %d startup(s) dormant, reactivated %d, deleted %d dormant startup(s).',
            now()->toIso8601String(),
            $markedDormant,
            $reactivated,
            $deleted
        );
        Log::channel('stack')->info($message);
        file_put_contents(storage_path('logs/startup-website-check.log'), $message . "\n", FILE_APPEND | LOCK_EX);

        $this->info("Done. Marked dormant: {$markedDormant}, reactivated: {$reactivated}, deleted: {$deleted}.");
        return 0;
    }

    private int $reactivatedCount = 0;

    private function checkAndUpdateStartups(bool $force): int
    {
        $query = Startup::query()
            ->whereIn('status', [Startup::STATUS_ACTIVE, Startup::STATUS_DORMANT])
            ->whereNotNull('website')
            ->where('website', '!=', '');

        if (! $force) {
            $cutoff = now()->subDays(StartupWebsiteHealthService::CHECK_INTERVAL_DAYS);
            $query->where(function ($q) use ($cutoff) {
                $q->whereNull('website_last_checked_at')
                    ->orWhere('website_last_checked_at', '<=', $cutoff);
            });
        }

        $startups = $query->get();
        $markedDormant = 0;
        $failureThreshold = StartupWebsiteHealthService::CONSECUTIVE_FAILURES_BEFORE_DORMANT;

        foreach ($startups as $startup) {
            $url = $this->healthService->normalizeUrl($startup->website);
            if (! $url) {
                continue;
            }

            $reachable = $this->healthService->isUrlReachable($url);
            $failures = $reachable ? 0 : (int) $startup->website_consecutive_failures + 1;

            $updates = [
                'website_last_checked_at' => now(),
                'website_is_reachable' => $reachable,
                'website_consecutive_failures' => $failures,
            ];

            if ($reachable) {
                $this->line("  OK: {$startup->name}");
                if ($startup->status === Startup::STATUS_DORMANT) {
                    $updates['status'] = Startup::STATUS_ACTIVE;
                    $updates['dormant_at'] = null;
                    $this->reactivatedCount++;
                    $this->line('    -> Reactivated (website reachable again).');
                }
            } else {
                $this->line("  Fail ({$failures}/{$failureThreshold}): {$startup->name} ({$url})");
                if ($startup->status === Startup::STATUS_ACTIVE && $failures >= $failureThreshold) {
                    $updates['status'] = Startup::STATUS_DORMANT;
                    $updates['dormant_at'] = now();
                    $markedDormant++;
                    $this->line("    -> Marked dormant ({$failureThreshold} consecutive failures).");
                }
            }

            $startup->update($updates);
        }

        return $markedDormant;
    }

    private function deleteDormantStartups(): int
    {
        $cutoff = now()->subDays(StartupWebsiteHealthService::DORMANT_DAYS_BEFORE_DELETE);
        $toDelete = Startup::query()
            ->where('status', Startup::STATUS_DORMANT)
            ->whereNotNull('dormant_at')
            ->where('dormant_at', '<=', $cutoff)
            ->get();

        foreach ($toDelete as $startup) {
            $this->line('  Deleted (dormant > ' . StartupWebsiteHealthService::DORMANT_DAYS_BEFORE_DELETE . " days): {$startup->name}");
            $startup->delete();
        }

        return $toDelete->count();
    }
}
