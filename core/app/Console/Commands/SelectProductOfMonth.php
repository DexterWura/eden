<?php

namespace App\Console\Commands;

use App\Services\StartupAwardService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SelectProductOfMonth extends Command
{
    protected $signature = 'eden:select-product-of-month
                            {--month= : Award month (Y-m), defaults to the previous month}';

    protected $description = 'Select product of the month from upvotes cast during a calendar month';

    public function handle(StartupAwardService $awardService): int
    {
        $monthInput = trim((string) $this->option('month'));
        if ($monthInput !== '' && ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthInput)) {
            $this->error('The month option must use Y-m format.');

            return self::FAILURE;
        }
        $awardMonth = $monthInput !== ''
            ? Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth()
            : now()->subMonthNoOverflow()->startOfMonth();
        if ($awardMonth->greaterThanOrEqualTo(now()->startOfMonth())) {
            $this->error('Product of the month can only be selected for a completed calendar month.');

            return self::FAILURE;
        }
        $result = $awardService->selectProductOfMonth($awardMonth);
        if ($result === null) {
            $this->info('No product of the month selected for ' . $awardMonth->format('F Y') . ' (already recorded or no qualifying upvotes).');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Product of the month for %s: startup #%d (%d upvotes).',
            $awardMonth->format('F Y'),
            $result['startup_id'],
            $result['upvote_count']
        ));

        return self::SUCCESS;
    }
}
