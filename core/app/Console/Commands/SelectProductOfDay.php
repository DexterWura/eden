<?php

namespace App\Console\Commands;

use App\Services\StartupService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SelectProductOfDay extends Command
{
    protected $signature = 'eden:select-product-of-day
                            {--date= : Award date (Y-m-d), defaults to yesterday}';

    protected $description = 'Select product of the day from upvotes cast on a calendar day';

    public function handle(StartupService $startupService): int
    {
        $dateInput = $this->option('date');
        $awardDate = $dateInput !== null && $dateInput !== ''
            ? Carbon::parse($dateInput)->startOfDay()
            : now()->subDay()->startOfDay();

        $result = $startupService->selectProductOfDayForDate($awardDate);

        if ($result === null) {
            $this->info('No product of the day selected for ' . $awardDate->toDateString() . ' (already recorded or no qualifying upvotes).');

            return 0;
        }

        $this->info(sprintf(
            'Product of the day for %s: startup #%d (%d upvotes).',
            $awardDate->toDateString(),
            $result['startup_id'],
            $result['upvote_count']
        ));

        return 0;
    }
}
