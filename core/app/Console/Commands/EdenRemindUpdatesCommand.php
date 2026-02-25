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
                $editUrl = $startup->user->isAdmin()
                    ? route('admin.startups.edit', $startup)
                    : route('my.startups.edit', $startup->slug);
                try {
                    Mail::raw("Hi {$startup->user->name},\n\nPlease update your startup \"{$startup->name}\" on " . config('app.name') . ".\n\nVisit: " . $editUrl . "\n\nThanks.", function ($m) use ($startup) {
                        $m->to($startup->user->email)->subject('Update your startup');
                    });
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Eden reminder email failed.', [
                        'startup_id' => $startup->id,
                        'email' => $startup->user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        Cache::put('eden_last_reminder', now()->toDateTimeString(), 86400 * 30);
        $this->info('Reminder emails sent.');
        return self::SUCCESS;
    }
}
