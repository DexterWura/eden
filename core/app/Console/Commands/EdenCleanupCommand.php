<?php

namespace App\Console\Commands;

use App\Models\Startup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EdenCleanupCommand extends Command
{
    protected $signature = 'eden:cleanup';
    protected $description = 'Delete unverified seedlings older than 30 days';

    public function handle(): int
    {
        $deleted = Startup::whereNull('approved_at')
            ->whereNotNull('submitted_at')
            ->whereNull('user_id')
            ->where('submitted_at', '<', now()->subDays(30))
            ->delete();
        Cache::put('eden_last_cleanup', now()->toDateTimeString(), 86400 * 30);
        $this->info("Cleanup completed. Deleted {$deleted} startups.");
        return self::SUCCESS;
    }
}
