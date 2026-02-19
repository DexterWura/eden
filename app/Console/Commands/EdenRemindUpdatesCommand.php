<?php

namespace App\Console\Commands;

use App\Models\Startup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EdenRemindUpdatesCommand extends Command
{
    protected $signature = 'eden:remind-updates';
    protected $description = 'Email owners to update their startup (90 days)';

    public function handle(): int
    {
        $startups = Startup::whereNotNull('user_id')
            ->where(function ($q) {
                $q->whereNull('last_updated_at')->orWhere('last_updated_at', '<', now()->subDays(90));
            })
            ->with('user')
            ->get();

        foreach ($startups as $startup) {
            if ($startup->user && $startup->user->email) {
                try {
                    Mail::raw("Hi {$startup->user->name},\n\nPlease update your startup \"{$startup->name}\" on " . config('app.name') . ".\n\nVisit: " . route('admin.startups.edit', $startup) . "\n\nThanks.", function ($m) use ($startup) {
                        $m->to($startup->user->email)->subject('Update your startup');
                    });
                } catch (\Throwable $e) {
                    // skip
                }
            }
        }
        Cache::put('eden_last_reminder', now()->toDateTimeString(), 86400 * 30);
        $this->info('Reminder emails sent.');
        return self::SUCCESS;
    }
}
