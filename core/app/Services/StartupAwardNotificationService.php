<?php

namespace App\Services;

use App\Models\Startup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StartupAwardNotificationService
{
    public function __construct(
        private StartupRecipientService $recipientService
    ) {}

    /**
     * @return array{complete: bool}
     */
    public function send(Startup $startup, string $awardType, int $awardWinnerId, string $periodLabel): array
    {
        $awardName = match ($awardType) {
            'day' => 'Product of the Day',
            'month' => 'Product of the Month',
            'year' => 'Product of the Year',
            default => throw new \InvalidArgumentException('Unsupported startup award type.'),
        };
        $recipients = $this->recipientService->founderRecipients($startup);
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        foreach ($recipients as $recipient) {
            DB::table('startup_award_notification_deliveries')->insertOrIgnore([
                'award_type' => $awardType,
                'award_winner_id' => $awardWinnerId,
                'recipient_email' => $recipient['email'],
                'recipient_name' => $recipient['name'],
                'user_id' => $recipient['user_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $delivery = DB::table('startup_award_notification_deliveries')
                ->where('award_type', $awardType)
                ->where('award_winner_id', $awardWinnerId)
                ->where('recipient_email', $recipient['email'])
                ->first();
            if (! $delivery) {
                continue;
            }
            if ($recipient['user_id'] && $delivery->dashboard_created_at === null) {
                $this->deliverDashboardNotification($startup, $recipient, $awardName, $periodLabel, $delivery->id);
            }
            if ($delivery->email_sent_at === null) {
                $this->deliverAwardEmail(
                    $startup,
                    $recipient,
                    $siteName,
                    $awardName,
                    $periodLabel,
                    $delivery->id
                );
            }
        }

        $hasIncompleteDelivery = DB::table('startup_award_notification_deliveries')
            ->where('award_type', $awardType)
            ->where('award_winner_id', $awardWinnerId)
            ->where(function ($query) {
                $query->whereNull('email_sent_at')
                    ->orWhere(function ($dashboardQuery) {
                        $dashboardQuery->whereNotNull('user_id')->whereNull('dashboard_created_at');
                    });
            })
            ->exists();

        return [
            'complete' => ! $hasIncompleteDelivery,
        ];
    }

    private function deliverDashboardNotification(
        Startup $startup,
        array $recipient,
        string $awardName,
        string $periodLabel,
        int $deliveryId
    ): void {
        try {
            $hash = md5("startup-award:{$startup->id}:{$awardName}:{$periodLabel}:{$recipient['user_id']}");
            $notificationId = substr($hash, 0, 8) . '-'
                . substr($hash, 8, 4) . '-'
                . substr($hash, 12, 4) . '-'
                . substr($hash, 16, 4) . '-'
                . substr($hash, 20, 12);
            DB::table('notifications')->updateOrInsert(['id' => $notificationId], [
                'type' => 'App\\Notifications\\StartupAwarded',
                'notifiable_type' => User::class,
                'notifiable_id' => $recipient['user_id'],
                'data' => json_encode([
                    'title' => "Your startup won {$awardName}!",
                    'message' => "{$startup->name} was selected as {$awardName} for {$periodLabel}.",
                    'url' => url('/startup/' . $startup->slug),
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('startup_award_notification_deliveries')
                ->where('id', $deliveryId)
                ->update(['dashboard_created_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function deliverAwardEmail(
        Startup $startup,
        array $recipient,
        string $siteName,
        string $awardName,
        string $periodLabel,
        int $deliveryId
    ): void {
        try {
            $html = view('emails.startup-award', [
                'recipientName' => $recipient['name'],
                'startup' => $startup,
                'siteName' => $siteName,
                'awardName' => $awardName,
                'periodLabel' => $periodLabel,
                'startupUrl' => url('/startup/' . $startup->slug),
                'badgesUrl' => url('/founder/badges'),
            ])->render();
            $subject = "{$startup->name} is {$awardName} on {$siteName}";
            Mail::html($html, function ($message) use ($recipient, $subject): void {
                $message->to($recipient['email'], $recipient['name'])->subject($subject);
            });
            DB::table('startup_award_notification_deliveries')
                ->where('id', $deliveryId)
                ->update(['email_sent_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
