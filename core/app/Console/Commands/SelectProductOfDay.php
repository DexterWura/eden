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
        $dateInput = trim((string) $this->option('date'));
        if ($dateInput !== '' && ! preg_match('/^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$/', $dateInput)) {
            $this->error('The date option must use Y-m-d format.');

            return self::FAILURE;
        }
        if ($dateInput !== '') {
            [$year, $month, $day] = array_map('intval', explode('-', $dateInput));
            if (! checkdate($month, $day, $year)) {
                $this->error('The date option must be a valid calendar date.');

                return self::FAILURE;
            }
        }
        $awardDate = $dateInput !== ''
            ? Carbon::parse($dateInput)->startOfDay()
            : now()->subDay()->startOfDay();
        if ($awardDate->greaterThanOrEqualTo(now()->startOfDay())) {
            $this->error('Product of the day can only be selected for a completed calendar day.');

            return self::FAILURE;
        }

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
