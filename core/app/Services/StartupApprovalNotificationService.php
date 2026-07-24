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
    public function send(Startup $startup): int
    {
        $recipients = $this->approvalRecipients($startup);
        $this->createDashboardNotifications($startup, $recipients);
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';

        $sent = 0;
        foreach ($recipients as $recipient) {
            try {
                $html = view('emails.startup-approved', [
                    'recipientName' => $recipient['name'],
                    'startup' => $startup,
                    'siteName' => $siteName,
                    'startupUrl' => url('/startup/' . $startup->slug),
                    'badgesUrl' => url('/founder/badges'),
                    'dashboardUrl' => url('/founder/startups'),
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

    private function approvalRecipients(Startup $startup): Collection
    {
        $recipients = collect();
        if ($startup->user_id) {
            $owner = User::query()->find($startup->user_id);
            if ($owner && filter_var($owner->email, FILTER_VALIDATE_EMAIL)) {
                $recipients->put(strtolower($owner->email), [
                    'email' => strtolower($owner->email),
                    'name' => $owner->name ?: 'Founder',
                    'user_id' => $owner->id,
                ]);
            }
        }

        $founders = $startup->founders ?? [];
        if ($startup->founder_email) {
            $founders[] = [
                'name' => $startup->founder_name,
                'email' => $startup->founder_email,
            ];
        }
        foreach ($founders as $founder) {
            $email = strtolower(trim((string) (is_array($founder) ? ($founder['email'] ?? '') : ($founder->email ?? ''))));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $recipients->has($email)) {
                continue;
            }
            $name = trim((string) (is_array($founder) ? ($founder['name'] ?? '') : ($founder->name ?? '')));
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            $recipients->put($email, [
                'email' => $email,
                'name' => $name !== '' ? $name : ($user?->name ?: 'Founder'),
                'user_id' => $user?->id,
            ]);
        }

        return $recipients->values();
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
