<?php

namespace App\Console\Commands;

use App\Models\Startup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EdenHealthCheckCommand extends Command
{
    protected $signature = 'eden:health-check';
    protected $description = 'Ping startup URLs and mark wilted after 7 days of failure';

    public function handle(): int
    {
        $startups = Startup::whereNotNull('url')->where('url', '!=', '')->get();
        foreach ($startups as $startup) {
            $url = $startup->url;
            if (! str_starts_with(strtolower($url), 'http')) {
                $url = 'https://' . ltrim($url, '/');
            }
            try {
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $startup->update(['url_failure_count' => 0, 'last_url_failure_at' => null]);
                } else {
                    $count = ($startup->url_failure_count ?? 0) + 1;
                    $startup->update([
                        'url_failure_count' => $count,
                        'last_url_failure_at' => now(),
                    ]);
                    if ($count >= 7) {
                        $startup->update(['status' => Startup::STATUS_WILTED]);
                    }
                }
            } catch (\Throwable $e) {
                $count = ($startup->url_failure_count ?? 0) + 1;
                $startup->update([
                    'url_failure_count' => $count,
                    'last_url_failure_at' => now(),
                ]);
                if ($count >= 7) {
                    $startup->update(['status' => Startup::STATUS_WILTED]);
                }
            }
        }
        Cache::put('eden_last_health_check', now()->toDateTimeString(), 86400 * 30);
        $this->info('Health check completed.');
        return self::SUCCESS;
    }
}
