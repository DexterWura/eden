<?php

namespace App\Services;

use App\Models\Startup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StartupApprovalNotificationService
{
    public function __construct(
        private StartupRecipientService $recipientService
    ) {}

    public function send(Startup $startup): int
    {
        $startup->loadMissing('user:id,is_pro');
        $recipients = $this->recipientService->founderRecipients($startup);
        $this->createDashboardNotifications($startup, $recipients);
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $hasDofollowBacklink = $startup->hasDofollowBacklink();

        $sent = 0;
        foreach ($recipients as $recipient) {
            if ($recipient['user_id']) {
                $user = User::query()->find($recipient['user_id']);
                if ($user && ! $user->wantsNotification('STARTUP_APPROVED')) {
                    continue;
                }
            }
            try {
                $html = view('emails.startup-approved', [
                    'recipientName' => $recipient['name'],
                    'startup' => $startup,
                    'siteName' => $siteName,
                    'startupUrl' => url('/startup/' . $startup->slug),
                    'badgesUrl' => url('/founder/badges'),
                    'dashboardUrl' => url('/founder/startups'),
                    'pricingUrl' => url('/pricing'),
                    'hasDofollowBacklink' => $hasDofollowBacklink,
                ])->render();
                $subject = $startup->name . ' is now live on ' . $siteName;

                Mail::html($html, function ($message) use ($recipient, $subject): void {
                    $message->to($recipient['email'], $recipient['name'])->subject($subject);
                });
                $sent++;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $sent;
    }

    private function createDashboardNotifications(Startup $startup, Collection $recipients): void
    {
        $userIds = $recipients->pluck('user_id')->filter()->unique();
        foreach ($userIds as $userId) {
            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\StartupApproved',
                'notifiable_type' => User::class,
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'title' => 'Your startup is live!',
                    'message' => "{$startup->name} is approved. Add your Eden badge from the Badges page to support the community.",
                    'url' => url('/founder/badges'),
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
