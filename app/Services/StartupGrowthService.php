<?php

namespace App\Services;

use App\Models\Startup;

class StartupGrowthService
{
    public function recalculateStatus(Startup $startup): void
    {
        $votesCount = $startup->votes()->count();
        $claimed = $startup->user_id !== null;
        $mrr = (float) $startup->mrr;
        $lastUpdated = $startup->last_updated_at;

        $newStatus = Startup::STATUS_SEEDLING;

        if ($startup->status === Startup::STATUS_WILTED) {
            return;
        }

        if ($claimed && $votesCount >= 10) {
            $newStatus = Startup::STATUS_SAPLING;
        }

        if ($claimed && ($mrr > 0 || ($lastUpdated && $lastUpdated->isAfter(now()->subDays(30))))) {
            $newStatus = Startup::STATUS_FLOURISHING;
        }

        if ($startup->status !== $newStatus) {
            $startup->update(['status' => $newStatus]);
        }
    }
}
