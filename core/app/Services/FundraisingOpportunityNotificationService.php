<?php

namespace App\Services;

use App\Models\Startup;
use App\Models\StartupFundingRound;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FundraisingOpportunityNotificationService
{
    public function sendOnce(StartupFundingRound $fundingRound): int
    {
        return DB::transaction(function () use ($fundingRound): int {
            $lockedRound = StartupFundingRound::query()
                ->with('startup')
                ->lockForUpdate()
                ->find($fundingRound->id);
            if (
                ! $lockedRound
                || $lockedRound->opportunity_announced_at !== null
                || $lockedRound->startup->status !== Startup::STATUS_ACTIVE
            ) {
                return 0;
            }

            $recipients = $this->registeredFounderRecipients($lockedRound->startup);
            $recipientIds = $recipients->pluck('id');
            $cancelledDeliveries = DB::table('fundraising_opportunity_deliveries')
                ->where('startup_funding_round_id', $lockedRound->id)
                ->whereNull('email_sent_at')
                ->whereNull('cancelled_at');
            if ($recipientIds->isNotEmpty()) {
                $cancelledDeliveries->whereNotIn('user_id', $recipientIds);
                DB::table('fundraising_opportunity_deliveries')
                    ->where('startup_funding_round_id', $lockedRound->id)
                    ->whereIn('user_id', $recipientIds)
                    ->whereNull('email_sent_at')
                    ->update(['cancelled_at' => null, 'updated_at' => now()]);
            }
            $cancelledDeliveries->update(['cancelled_at' => now(), 'updated_at' => now()]);

            $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
            $sent = 0;
            foreach ($recipients as $recipient) {
                DB::table('fundraising_opportunity_deliveries')->insertOrIgnore([
                    'startup_funding_round_id' => $lockedRound->id,
                    'user_id' => $recipient->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $delivery = DB::table('fundraising_opportunity_deliveries')
                    ->where('startup_funding_round_id', $lockedRound->id)
                    ->where('user_id', $recipient->id)
                    ->first();
                if (! $delivery || $delivery->email_sent_at !== null) {
                    continue;
                }

                try {
                    $html = view('emails.fundraising-opportunity', [
                        'recipientName' => $recipient->name ?: 'Founder',
                        'startup' => $lockedRound->startup,
                        'fundingRound' => $lockedRound,
                        'siteName' => $siteName,
                        'startupUrl' => url('/startup/' . $lockedRound->startup->slug),
                    ])->render();
                    $subject = $lockedRound->startup->name . ' is raising funding on ' . $siteName;
                    Mail::html($html, function ($message) use ($recipient, $subject): void {
                        $message->to($recipient->email, $recipient->name)->subject($subject);
                    });
                    DB::table('fundraising_opportunity_deliveries')
                        ->where('id', $delivery->id)
                        ->update(['email_sent_at' => now(), 'updated_at' => now()]);
                    $sent++;
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }

            $hasPendingDelivery = DB::table('fundraising_opportunity_deliveries')
                ->where('startup_funding_round_id', $lockedRound->id)
                ->whereNull('email_sent_at')
                ->whereNull('cancelled_at')
                ->exists();
            if (! $hasPendingDelivery) {
                $lockedRound->update(['opportunity_announced_at' => now()]);
            }

            return $sent;
        });
    }

    private function registeredFounderRecipients(Startup $raisingStartup): Collection
    {
        $activeStartups = Startup::active()->get(['user_id', 'founders']);
        $userIds = $activeStartups->pluck('user_id')->filter()->map(fn ($id) => (int) $id);
        foreach ($activeStartups as $startup) {
            foreach ($startup->founders ?? [] as $founder) {
                $founderUserId = is_array($founder) ? ($founder['user_id'] ?? null) : ($founder->user_id ?? null);
                if ($founderUserId) {
                    $userIds->push((int) $founderUserId);
                }
            }
        }

        $excludedUserIds = collect([(int) $raisingStartup->user_id])->filter();
        $excludedEmails = collect([(string) $raisingStartup->founder_email])
            ->merge(collect($raisingStartup->founders ?? [])->map(function ($founder) {
                return is_array($founder) ? ($founder['email'] ?? null) : ($founder->email ?? null);
            }))
            ->filter()
            ->map(fn ($email) => strtolower(trim((string) $email)));
        foreach ($raisingStartup->founders ?? [] as $founder) {
            $founderUserId = is_array($founder) ? ($founder['user_id'] ?? null) : ($founder->user_id ?? null);
            if ($founderUserId) {
                $excludedUserIds->push((int) $founderUserId);
            }
        }

        return User::query()
            ->active()
            ->whereIn('id', $userIds->unique()->values())
            ->whereNotIn('id', $excludedUserIds->unique()->values())
            ->get()
            ->filter(function (User $user) use ($excludedEmails): bool {
                return filter_var($user->email, FILTER_VALIDATE_EMAIL)
                    && ! $excludedEmails->contains(strtolower($user->email))
                    && $user->wantsNotification('FUNDRAISING_OPPORTUNITIES');
            })
            ->values();
    }
}
