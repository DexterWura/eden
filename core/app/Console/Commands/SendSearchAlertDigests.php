<?php

namespace App\Console\Commands;

use App\Models\SearchAlertSubscription;
use App\Services\StartupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSearchAlertDigests extends Command
{
    protected $signature = 'eden:search-alert-digests
                            {--dry-run : Log matches only, do not send email}';

    protected $description = 'Email subscribers when new startups match their saved search filters (weekly)';

    public function handle(StartupService $startupService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';

        $sent = 0;
        foreach (SearchAlertSubscription::query()->orderBy('id')->cursor() as $sub) {
            $since = $sub->last_notified_at ?? $sub->created_at;
            $startups = $startupService->activeStartupsMatchingFiltersSince(
                $sub->search_query,
                $sub->category,
                $sub->location,
                $since
            );

            if ($dryRun) {
                $this->line(
                    ($startups->isEmpty() ? '[skip] ' : '[send] ')
                    . $sub->email . ' — ' . $startups->count() . ' match(es), ' . $sub->summaryLabel()
                );

                continue;
            }

            if ($startups->isEmpty()) {
                $sub->update(['last_notified_at' => now()]);

                continue;
            }

            $subject = $siteName . ' – ' . $startups->count() . ' new ' . ($startups->count() === 1 ? 'listing' : 'listings') . ' match your search';
            $html = view('eden.subscribers.search-alert-digest', [
                'startups' => $startups,
                'siteName' => $siteName,
                'subscription' => $sub,
                'unsubscribeUrl' => route('search-alerts.unsubscribe', ['token' => $sub->unsubscribe_token]),
            ])->render();

            try {
                Mail::html($html, function ($message) use ($sub, $subject): void {
                    $message->to($sub->email)->subject($subject);
                });
                $sub->update(['last_notified_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Search alert digest failed for ' . $sub->email, ['exception' => $e->getMessage()]);
                $this->warn('Failed: ' . $sub->email . ' — ' . $e->getMessage());
            }
        }

        $this->info('Search alert digests sent: ' . $sent);

        return self::SUCCESS;
    }
}
