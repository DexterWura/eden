<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Auto-check for pending migrations (check only, don't auto-run in production)
Schedule::command('migrate:auto --check-only')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/migration-check.log'));

// Auto-run migrations only in non-production environments
if (app()->environment(['local', 'staging', 'development'])) {
    Schedule::command('migrate:auto')
        ->hourly()
        ->withoutOverlapping()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/migration-auto.log'));
}

// Set cache indicator when schedule runs (for cron detection)
Schedule::call(function () {
    \Illuminate\Support\Facades\Cache::put('schedule:run:last', now()->toIso8601String(), now()->addMinutes(10));
})->everyMinute();

// Eden scheduled tasks (sitemap, etc.) - admin-configurable intervals
Schedule::command('eden:run-scheduled-tasks')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/eden-scheduled-tasks.log'));

// Process ending auctions - run every minute (always enabled)
Schedule::command('auctions:process-ending --minutes=5')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/auction-processing.log'));

// Marketplace cleanup - run daily at 2 AM
Schedule::command('marketplace:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/marketplace-cleanup.log'));

// NDA expiration reminders - run daily at 9 AM
Schedule::command('nda:expiration-reminders')
    ->dailyAt('09:00')
    ->name('nda-expiration-reminders')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/nda-expiration-reminders.log'));

// Process expired NDAs - run daily at midnight
Schedule::command('nda:process-expired')
    ->dailyAt('00:00')
    ->name('nda-expiration-processing')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/nda-expiration-processing.log'));

// Monthly revenue report - run at end of every month (last day at 23:55)
Schedule::command('monthly:revenue-report')
    ->lastDayOfMonth('23:55')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/monthly-revenue-report.log'));

// Startup website check - ping active startups weekly; mark failing as dormant, delete after 1 week dormant
Schedule::command('startups:check-websites')
    ->weekly()
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/startup-website-check.log'));
