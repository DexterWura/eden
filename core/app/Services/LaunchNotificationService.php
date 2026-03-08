<?php

namespace App\Services;

use App\Models\LaunchNotification;
use App\Models\Startup;
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

        $sent = 0;
        foreach ($subscribers as $sub) {
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
