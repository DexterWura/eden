<?php

namespace App\Console\Commands;

use App\Models\Startup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckStartupWebsites extends Command
{
    protected $signature = 'startups:check-websites';

    protected $description = 'Ping active startup websites weekly; mark failing as dormant, delete after 1 week dormant';

    private const PING_TIMEOUT_SECONDS = 10;

    private const DORMANT_DAYS_BEFORE_DELETE = 7;

    public function handle(): int
    {
        $logFile = storage_path('logs/startup-website-check.log');
        $this->info('Startup website check started.');

        $markedDormant = $this->markFailingStartupsDormant();
        $deleted = $this->deleteDormantStartups();

        $message = sprintf(
            '[%s] Marked %d startup(s) dormant, deleted %d dormant startup(s).',
            now()->toIso8601String(),
            $markedDormant,
            $deleted
        );
        Log::channel('stack')->info($message);
        file_put_contents($logFile, $message . "\n", FILE_APPEND | LOCK_EX);

        $this->info("Done. Marked dormant: {$markedDormant}, deleted: {$deleted}.");
        return 0;
    }

    private function markFailingStartupsDormant(): int
    {
        $startups = Startup::query()
            ->where('status', Startup::STATUS_ACTIVE)
            ->whereNotNull('website')
            ->where('website', '!=', '')
            ->get();

        $count = 0;
        foreach ($startups as $startup) {
            $url = $this->normalizeUrl($startup->website);
            if (!$url || !$this->pingUrl($url)) {
                $startup->update([
                    'status' => Startup::STATUS_DORMANT,
                    'dormant_at' => now(),
                ]);
                $count++;
                $this->line("  Marked dormant (ping failed): {$startup->name} ({$url})");
            }
        }

        return $count;
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
                ->connectTimeout(5)
                ->withOptions(['verify' => false])
                ->get($url);

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
