<?php

namespace App\Console\Commands;

use App\Models\Startup;
use App\Models\Subscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWeeklyNewStartupsDigest extends Command
{
    protected $signature = 'eden:weekly-digest
                            {--dry-run : Build and log only, do not send emails}';

    protected $description = 'Send weekly email to subscribers with new startups from the last 7 days';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $since = now()->subDays(7);

        $startups = Startup::query()
            ->where('status', Startup::STATUS_ACTIVE)
            ->where('created_at', '>=', $since)
            ->with('activeFundingRound')
            ->orderByDesc('created_at')
            ->get();

        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $subject = $siteName . ' – New apps this week (' . $startups->count() . ')';

        $html = view('eden.subscribers.weekly-digest', [
            'startups' => $startups,
            'siteName' => $siteName,
            'since' => $since,
        ])->render();

        $subscribers = Subscriber::pluck('email');
        $count = $subscribers->count();

        if ($count === 0) {
            $this->info('No subscribers to send to.');
            return 0;
        }

        if ($dryRun) {
            $this->info('Dry run: would send "' . $subject . '" to ' . $count . ' subscriber(s). Startups: ' . $startups->count());
            return 0;
        }

        $sent = 0;
        foreach ($subscribers as $email) {
            try {
                Mail::html($html, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Weekly digest send failed for ' . $email, ['exception' => $e->getMessage()]);
                $this->warn('Failed to send to ' . $email . ': ' . $e->getMessage());
            }
        }

        $this->info('Sent weekly digest to ' . $sent . ' subscriber(s).');
        return 0;
    }
}
