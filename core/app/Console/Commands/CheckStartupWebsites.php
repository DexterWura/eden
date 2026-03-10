<?php

namespace App\Console\Commands;

use App\Models\Startup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckStartupWebsites extends Command
{
    protected $signature = 'startups:check-websites
                            {--force : Check all startups with a website, ignoring the 3-day interval}';

    protected $description = 'Ping startup websites (every 3 days per startup); mark dormant after 3 consecutive failures; delete if dormant too long';

    private const PING_TIMEOUT_SECONDS = 25;

    private const CHECK_INTERVAL_DAYS = 3;

    private const CONSECUTIVE_FAILURES_BEFORE_DORMANT = 3;

    private const DORMANT_DAYS_BEFORE_DELETE = 30;

    public function handle(): int
    {
        $force = $this->option('force');
        $this->info('Startup website check started (interval: ' . self::CHECK_INTERVAL_DAYS . ' days, dormant after ' . self::CONSECUTIVE_FAILURES_BEFORE_DORMANT . ' failures).');

        $markedDormant = $this->checkAndUpdateStartups($force);
        $deleted = $this->deleteDormantStartups();

        $message = sprintf(
            '[%s] Marked %d startup(s) dormant, deleted %d dormant startup(s).',
            now()->toIso8601String(),
            $markedDormant,
            $deleted
        );
        Log::channel('stack')->info($message);
        $logFile = storage_path('logs/startup-website-check.log');
        file_put_contents($logFile, $message . "\n", FILE_APPEND | LOCK_EX);

        $this->info("Done. Marked dormant: {$markedDormant}, deleted: {$deleted}.");
        return 0;
    }

    private function checkAndUpdateStartups(bool $force): int
    {
        $query = Startup::query()
            ->where('status', Startup::STATUS_ACTIVE)
            ->whereNotNull('website')
            ->where('website', '!=', '');

        if (!$force) {
            $cutoff = now()->subDays(self::CHECK_INTERVAL_DAYS);
            $query->where(function ($q) use ($cutoff) {
                $q->whereNull('website_last_checked_at')
                    ->orWhere('website_last_checked_at', '<=', $cutoff);
            });
        }

        $startups = $query->get();
        $markedDormant = 0;

        foreach ($startups as $startup) {
            $url = $this->normalizeUrl($startup->website);
            if (!$url) {
                continue;
            }

            $reachable = $this->pingUrl($url);
            $failures = $reachable ? 0 : (int) $startup->website_consecutive_failures + 1;

            $updates = [
                'website_last_checked_at' => now(),
                'website_is_reachable' => $reachable,
                'website_consecutive_failures' => $failures,
            ];

            if ($reachable) {
                $this->line("  OK: {$startup->name}");
            } else {
                $this->line("  Fail ({$failures}): {$startup->name} ({$url})");
                if ($failures >= self::CONSECUTIVE_FAILURES_BEFORE_DORMANT) {
                    $updates['status'] = Startup::STATUS_DORMANT;
                    $updates['dormant_at'] = now();
                    $markedDormant++;
                    $this->line("    -> Marked dormant (3 consecutive failures).");
                }
            }

            $startup->update($updates);
        }

        return $markedDormant;
    }

    private function deleteDormantStartups(): int
    {
        $cutoff = now()->subDays(self::DORMANT_DAYS_BEFORE_DELETE);
        $toDelete = Startup::query()
            ->where('status', Startup::STATUS_DORMANT)
            ->whereNotNull('dormant_at')
            ->where('dormant_at', '<=', $cutoff)
            ->get();

        foreach ($toDelete as $startup) {
            $this->line("  Deleted (dormant > " . self::DORMANT_DAYS_BEFORE_DELETE . " days): {$startup->name}");
            $startup->delete();
        }

        return $toDelete->count();
    }

    private function normalizeUrl(?string $website): ?string
    {
        if ($website === null || trim($website) === '') {
            return null;
        }
        $url = trim($website);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    private function pingUrl(string $url): bool
    {
        try {
            $response = Http::timeout(self::PING_TIMEOUT_SECONDS)
                ->connectTimeout(10)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                return true;
            }
        } catch (\Throwable $e) {
            // fall through to retry below
        }

        // Retry once after a short delay to tolerate transient issues
        sleep(2);

        try {
            $response = Http::timeout(self::PING_TIMEOUT_SECONDS)
                ->connectTimeout(10)
                ->withOptions(['verify' => false])
                ->get($url);

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
