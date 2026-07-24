<?php

namespace App\Services;

use App\Models\LaunchNotification;
use App\Models\Startup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LaunchNotificationService
{
    public function sendLaunchEmails(Startup $startup): int
    {
        $subscribers = LaunchNotification::where('startup_id', $startup->id)->get();
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $startupUrl = url('/startup/' . $startup->slug);
        $startupName = $startup->name;
        $tagline = $startup->tagline ?: $startup->short_description;
        $registeredUsers = User::query()
            ->whereIn(DB::raw('LOWER(email)'), $subscribers->pluck('email')->map(
                fn (string $email): string => mb_strtolower($email)
            )->unique())
            ->get()
            ->keyBy(fn (User $user): string => mb_strtolower($user->email));

        $sent = 0;
        foreach ($subscribers as $sub) {
            $registeredUser = $registeredUsers->get(mb_strtolower($sub->email));
            if ($registeredUser && ! $registeredUser->wantsNotification('LAUNCH_UPDATES')) {
                continue;
            }
            try {
                $html = view('eden.launch-notification-email', [
                    'startupName' => $startupName,
                    'tagline' => $tagline,
                    'startupUrl' => $startupUrl,
                    'siteName' => $siteName,
                ])->render();
                Mail::html($html, function ($message) use ($sub, $startupName, $siteName) {
                    $message->to($sub->email)->subject($startupName . ' is now live on ' . $siteName);
                });
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
            $sub->delete();
        }

        return $sent;
    }
}
