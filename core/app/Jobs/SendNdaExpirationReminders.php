<?php

namespace App\Jobs;

use App\Models\NdaDocument;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendNdaExpirationReminders
{
    use Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find NDAs expiring in 7 days
        $sevenDaysFromNow = now()->addDays(7)->startOfDay();
        $ndasExpiringIn7Days = NdaDocument::where('status', 'signed')
            ->whereDate('expires_at', $sevenDaysFromNow->format('Y-m-d'))
            ->with(['user', 'listing'])
            ->get();

        foreach ($ndasExpiringIn7Days as $nda) {
            try {
                notify($nda->user, 'NDA_EXPIRING_SOON', [
                    'listing_title' => $nda->listing->title,
                    'expires_at' => $nda->expires_at->format('F d, Y'),
                    'days_remaining' => 7,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send 7-day expiration reminder', [
                    'nda_id' => $nda->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Find NDAs expiring in 1 day
        $oneDayFromNow = now()->addDay()->startOfDay();
        $ndasExpiringIn1Day = NdaDocument::where('status', 'signed')
            ->whereDate('expires_at', $oneDayFromNow->format('Y-m-d'))
            ->with(['user', 'listing'])
            ->get();

        foreach ($ndasExpiringIn1Day as $nda) {
            try {
                notify($nda->user, 'NDA_EXPIRING_SOON', [
                    'listing_title' => $nda->listing->title,
                    'expires_at' => $nda->expires_at->format('F d, Y'),
                    'days_remaining' => 1,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send 1-day expiration reminder', [
                    'nda_id' => $nda->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
