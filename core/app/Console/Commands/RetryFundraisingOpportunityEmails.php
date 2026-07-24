<?php

namespace App\Console\Commands;

use App\Models\Startup;
use App\Models\StartupFundingRound;
use App\Services\FundraisingOpportunityNotificationService;
use Illuminate\Console\Command;

class RetryFundraisingOpportunityEmails extends Command
{
    protected $signature = 'eden:retry-fundraising-opportunity-emails';

    protected $description = 'Retry incomplete founder investment opportunity emails';

    public function handle(FundraisingOpportunityNotificationService $notificationService): int
    {
        $roundsProcessed = 0;
        $emailsSent = 0;
        StartupFundingRound::query()
            ->open()
            ->whereNull('opportunity_announced_at')
            ->whereHas('startup', fn ($query) => $query->where('status', Startup::STATUS_ACTIVE))
            ->orderBy('id')
            ->chunkById(25, function ($fundingRounds) use (
                $notificationService,
                &$roundsProcessed,
                &$emailsSent
            ): void {
                foreach ($fundingRounds as $fundingRound) {
                    $emailsSent += $notificationService->sendOnce($fundingRound);
                    $roundsProcessed++;
                }
            });

        $this->info("Processed {$roundsProcessed} funding rounds and sent {$emailsSent} emails.");

        return self::SUCCESS;
    }
}
