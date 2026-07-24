<?php

namespace App\Services;

use App\Models\Startup;
use App\Models\StartupFundingRound;

class StartupFundingRoundService
{
    public function sync(Startup $startup, array $input): void
    {
        $seekingInvestors = ($input['seeking_investors'] ?? '0') === '1';
        $openRound = $startup->activeFundingRound;
        if (! $seekingInvestors) {
            if ($openRound) {
                $openRound->update(['status' => StartupFundingRound::STATUS_CLOSED]);
            }
            return;
        }

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

        if ($openRound) {
            $openRound->update($payload);
        } else {
            StartupFundingRound::create(array_merge(['startup_id' => $startup->id], $payload));
        }
    }
}
