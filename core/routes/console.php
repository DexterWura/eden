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

// Daily sitemap generation - ensure sitemap.xml is regenerated once per day
Schedule::command('sitemap:generate')
    ->dailyAt('01:00')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/sitemap-generate.log'));

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

// Product of the day - lock in yesterday's winner from daily upvotes
Schedule::command('eden:select-product-of-day')
    ->dailyAt('00:05')
    ->withoutOverlapping(5)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/eden-product-of-day.log'));

// Product of the month - lock in the previous calendar month's winner
Schedule::command('eden:select-product-of-month')
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping(10)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/eden-product-of-month.log'));

// Product of the year - lock in the previous calendar year's winner
Schedule::command('eden:select-product-of-year')
    ->yearlyOn(1, 1, '00:15')
    ->withoutOverlapping(10)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/eden-product-of-year.log'));

// Monthly revenue report - run at end of every month (last day at 23:55)
Schedule::command('monthly:revenue-report')
    ->lastDayOfMonth('23:55')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/monthly-revenue-report.log'));

// Startup website check - ping each startup every 3 days; mark dormant after 6 consecutive failures; reactivate when reachable; delete after 30 days dormant
Schedule::command('startups:check-websites')
    ->daily()
    ->withoutOverlapping(5)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/startup-website-check.log'));

// Eden revenue sync - pull from Stripe, Polar, Lemon Squeezy
Schedule::command('revenue:sync')
    ->dailyAt('03:00')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/revenue-sync.log'));

// Eden weekly digest - new startups email to subscribers (Mondays 9:00)
Schedule::command('eden:weekly-digest')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping(10)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/eden-weekly-digest.log'));

// Saved search alerts — new listings matching filters (Mondays 9:15, after weekly digest)
Schedule::command('eden:search-alert-digests')
    ->weeklyOn(1, '09:15')
    ->withoutOverlapping(10)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/eden-search-alert-digests.log'));

// Retry incomplete founder investment opportunity emails
Schedule::command('eden:retry-fundraising-opportunity-emails')
    ->everyTenMinutes()
    ->withoutOverlapping(10)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/eden-fundraising-opportunities.log'));
