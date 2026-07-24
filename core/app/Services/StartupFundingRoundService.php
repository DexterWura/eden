<?php

namespace App\Services;

use App\Models\Startup;
use App\Models\StartupFundingRound;
use Illuminate\Support\Facades\DB;

class StartupFundingRoundService
{
    public function __construct(
        private FundraisingOpportunityNotificationService $notificationService
    ) {}

    public function sync(Startup $startup, array $input): void
    {
        $seekingInvestors = ($input['seeking_investors'] ?? '0') === '1';
        $roundType = $input['funding_round_type'] ?? 'seed';
        if (! array_key_exists($roundType, StartupFundingRound::ROUND_TYPES)) {
            $roundType = 'seed';
        }
        $payload = [
            'round_type' => $roundType,
            'amount_seeking' => $input['funding_amount_seeking'] ?? null,
            'currency' => strtoupper(substr((string) ($input['funding_currency'] ?? 'USD'), 0, 3)) ?: 'USD',
            'description' => ($input['funding_description'] ?? null) ?: null,
            'contact_email' => ($input['funding_contact_email'] ?? null) ?: null,
            'status' => StartupFundingRound::STATUS_OPEN,
        ];

        $fundingRound = DB::transaction(function () use ($startup, $seekingInvestors, $payload) {
            $lockedStartup = Startup::query()->lockForUpdate()->findOrFail($startup->id);
            $openRound = StartupFundingRound::query()
                ->where('startup_id', $lockedStartup->id)
                ->open()
                ->lockForUpdate()
                ->first();
            if (! $seekingInvestors) {
                $openRound?->update(['status' => StartupFundingRound::STATUS_CLOSED]);
                return null;
            }
            if ($openRound) {
                $openRound->update($payload);
                return $openRound->fresh();
            }

            return StartupFundingRound::create(array_merge(['startup_id' => $lockedStartup->id], $payload));
        });

        if (
            $fundingRound
            && $fundingRound->startup()->where('status', Startup::STATUS_ACTIVE)->exists()
        ) {
            $this->notificationService->sendOnce($fundingRound);
        }
    }
}
