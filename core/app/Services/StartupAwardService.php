<?php

namespace App\Services;

use App\Models\ProductOfDayWinner;
use App\Models\ProductOfMonthWinner;
use App\Models\ProductOfYearWinner;
use App\Models\Startup;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class StartupAwardService
{
    public function __construct(
        private StartupAwardNotificationService $notificationService
    ) {}

    public function selectProductOfDay(CarbonInterface $awardDate): ?array
    {
        $date = $awardDate->copy()->startOfDay();
        if ($date->greaterThanOrEqualTo(now()->startOfDay())) {
            throw new \InvalidArgumentException('Daily awards require a completed calendar day.');
        }

        return $this->selectWinner(
            ProductOfDayWinner::class,
            'award_date',
            $date->toDateString(),
            $date,
            $date->copy()->endOfDay(),
            'product_of_day_at',
            $date->toDateString(),
            'day',
            $date->format('F j, Y')
        );
    }

    public function selectProductOfMonth(CarbonInterface $month): ?array
    {
        $start = $month->copy()->startOfMonth();
        if ($start->greaterThanOrEqualTo(now()->startOfMonth())) {
            throw new \InvalidArgumentException('Monthly awards require a completed calendar month.');
        }

        return $this->selectWinner(
            ProductOfMonthWinner::class,
            'award_month',
            $start->toDateString(),
            $start,
            $start->copy()->endOfMonth(),
            'product_of_month_at',
            $start->toDateString(),
            'month',
            $start->format('F Y')
        );
    }

    public function selectProductOfYear(CarbonInterface $year): ?array
    {
        $start = $year->copy()->startOfYear();
        if ($start->greaterThanOrEqualTo(now()->startOfYear())) {
            throw new \InvalidArgumentException('Yearly awards require a completed calendar year.');
        }

        return $this->selectWinner(
            ProductOfYearWinner::class,
            'award_year',
            (int) $start->year,
            $start,
            $start->copy()->endOfYear(),
            'product_of_year_at',
            (int) $start->year,
            'year',
            (string) $start->year
        );
    }

    /**
     * @param class-string<Model> $winnerModel
     * @return array{startup_id: int, upvote_count: int}|null
     */
    private function selectWinner(
        string $winnerModel,
        string $periodColumn,
        string|int $periodValue,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
        string $startupAwardColumn,
        string|int $startupAwardValue,
        string $awardType,
        string $periodLabel
    ): ?array {
        $existingWinner = $winnerModel::query()->where($periodColumn, $periodValue)->first();
        if ($existingWinner) {
            $this->notifyWinnerIfNeeded($winnerModel, $existingWinner, $awardType, $periodLabel);

            return null;
        }

        $winner = Startup::active()
            ->selectRaw(
                'startups.*, (SELECT COUNT(*) FROM startup_upvotes WHERE startup_upvotes.startup_id = startups.id AND startup_upvotes.created_at >= ? AND startup_upvotes.created_at <= ?) AS period_upvotes',
                [$periodStart, $periodEnd]
            )
            ->orderByDesc('period_upvotes')
            ->orderByDesc('upvotes')
            ->orderBy('id')
            ->first();
        if ($winner === null || (int) $winner->period_upvotes < 1) {
            return null;
        }

        $startupId = (int) $winner->id;
        $upvoteCount = (int) $winner->period_upvotes;
        try {
            $awardWinner = DB::transaction(function () use (
                $winnerModel,
                $periodColumn,
                $periodValue,
                $startupId,
                $upvoteCount,
                $startupAwardColumn,
                $startupAwardValue
            ): Model {
                $awardWinner = $winnerModel::query()->create([
                    $periodColumn => $periodValue,
                    'startup_id' => $startupId,
                    'upvote_count' => $upvoteCount,
                ]);
                Startup::query()
                    ->whereKey($startupId)
                    ->update([$startupAwardColumn => $startupAwardValue]);

                return $awardWinner;
            });
        } catch (QueryException $exception) {
            $existingWinner = $winnerModel::query()->where($periodColumn, $periodValue)->first();
            if (! $existingWinner) {
                throw $exception;
            }
            $this->notifyWinnerIfNeeded($winnerModel, $existingWinner, $awardType, $periodLabel);

            return null;
        }
        $this->notifyWinnerIfNeeded($winnerModel, $awardWinner, $awardType, $periodLabel);

        return [
            'startup_id' => $startupId,
            'upvote_count' => $upvoteCount,
        ];
    }

    /**
     * @param class-string<Model> $winnerModel
     */
    private function notifyWinnerIfNeeded(
        string $winnerModel,
        Model $awardWinner,
        string $awardType,
        string $periodLabel
    ): void {
        DB::transaction(function () use ($winnerModel, $awardWinner, $awardType, $periodLabel): void {
            $lockedWinner = $winnerModel::query()->lockForUpdate()->find($awardWinner->getKey());
            if (! $lockedWinner || $lockedWinner->notified_at !== null) {
                return;
            }
            $startup = Startup::query()->find($lockedWinner->startup_id);
            if (! $startup) {
                return;
            }
            $delivery = $this->notificationService->send(
                $startup,
                $awardType,
                (int) $lockedWinner->getKey(),
                $periodLabel
            );
            if ($delivery['complete']) {
                $lockedWinner->update(['notified_at' => now()]);
            }
        });
    }
}
