<?php

namespace App\Console\Commands;

use App\Services\StartupAwardService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SelectProductOfYear extends Command
{
    protected $signature = 'eden:select-product-of-year
                            {--year= : Award year (YYYY), defaults to the previous year}';

    protected $description = 'Select product of the year from upvotes cast during a calendar year';

    public function handle(StartupAwardService $awardService): int
    {
        $yearInput = trim((string) $this->option('year'));
        if ($yearInput !== '' && ! preg_match('/^\d{4}$/', $yearInput)) {
            $this->error('The year option must use YYYY format.');

            return self::FAILURE;
        }
        $awardYear = $yearInput !== ''
            ? Carbon::create((int) $yearInput, 1, 1)->startOfYear()
            : now()->subYear()->startOfYear();
        if ($awardYear->greaterThanOrEqualTo(now()->startOfYear())) {
            $this->error('Product of the year can only be selected for a completed calendar year.');

            return self::FAILURE;
        }
        $result = $awardService->selectProductOfYear($awardYear);
        if ($result === null) {
            $this->info('No product of the year selected for ' . $awardYear->year . ' (already recorded or no qualifying upvotes).');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Product of the year for %d: startup #%d (%d upvotes).',
            $awardYear->year,
            $result['startup_id'],
            $result['upvote_count']
        ));

        return self::SUCCESS;
    }
}
