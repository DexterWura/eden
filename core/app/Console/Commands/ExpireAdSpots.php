<?php

namespace App\Console\Commands;

use App\Models\AdSpot;
use Illuminate\Console\Command;

class ExpireAdSpots extends Command
{
    protected $signature = 'eden:expire-ad-spots';

    protected $description = 'Mark ad spots as expired when their end date has passed';

    public function handle(): int
    {
        $now = now();

        $updated = AdSpot::where('status', AdSpot::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->update(['status' => AdSpot::STATUS_EXPIRED]);

        $this->info("Expired {$updated} ad spot(s).");

        return self::SUCCESS;
    }
}

