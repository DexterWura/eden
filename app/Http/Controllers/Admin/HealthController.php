<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function index()
    {
        $lastHealthCheck = Cache::get('eden_last_health_check');
        $lastCleanup = Cache::get('eden_last_cleanup');
        $lastReminder = Cache::get('eden_last_reminder');
        $lastNewsletter = Cache::get('eden_last_newsletter');
        return view('admin.health.index', compact('lastHealthCheck', 'lastCleanup', 'lastReminder', 'lastNewsletter'));
    }

    public function run(string $command)
    {
        $allowed = ['health-check', 'cleanup', 'remind-updates', 'newsletter'];
        if (! in_array($command, $allowed, true)) {
            return back()->with('error', 'Invalid command.');
        }
        try {
            Artisan::call('eden:' . $command);
            Cache::put('eden_last_' . str_replace('-', '_', $command), now()->toDateTimeString(), 86400 * 30);
            return back()->with('success', 'Command run successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
