<?php

namespace App\Jobs;

use App\Models\NdaDocument;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessNdaExpiration
{
    use Dispatchable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find expired NDAs that are still marked as 'signed'
        $expiredNdas = NdaDocument::where('status', 'signed')
            ->where('expires_at', '<=', now())
            ->with(['user', 'listing'])
            ->get();

        foreach ($expiredNdas as $nda) {
            DB::beginTransaction();
            try {
                // Update status to expired
                $nda->status = 'expired';
                $nda->save();

                // Log expiration
                $nda->auditLogs()->create([
                    'action' => 'expired',
                    'user_id' => $nda->user_id,
                    'ip_address' => null,
                    'user_agent' => 'System',
                    'metadata' => [
                        'expired_at' => now()->toIso8601String(),
                        'original_expires_at' => $nda->expires_at->toIso8601String(),
                    ],
                ]);

                // Notify user
                try {
                    notify($nda->user, 'NDA_EXPIRED', [
                        'listing_title' => $nda->listing->title,
                        'expired_at' => $nda->expires_at->format('F d, Y'),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send expiration notification', [
                        'nda_id' => $nda->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Notify seller
                try {
                    notify($nda->listing->seller, 'NDA_EXPIRED_SELLER', [
                        'listing_title' => $nda->listing->title,
                        'signer' => $nda->user->username,
                        'signer_username' => $nda->user->username,
                        'signer_name' => $nda->user->fullname ?? $nda->user->username,
                        'expired_at' => $nda->expires_at->format('F d, Y'),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send expiration notification to seller', [
                        'nda_id' => $nda->id,
                        'error' => $e->getMessage()
                    ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to process NDA expiration', [
                    'nda_id' => $nda->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
