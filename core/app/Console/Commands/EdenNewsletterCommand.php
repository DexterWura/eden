<?php

namespace App\Console\Commands;

use App\Models\NewsletterSubscriber;
use App\Models\Startup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EdenNewsletterCommand extends Command
{
    protected $signature = 'eden:newsletter';
    protected $description = 'Send weekly Top 5 Flourishing startups to subscribers';

    public function handle(): int
    {
        $startups = Startup::approved()
            ->where('status', 'flourishing')
            ->orderByDesc('mrr')
            ->orderByDesc('last_updated_at')
            ->limit(5)
            ->get();

        $subscribers = NewsletterSubscriber::all();
        if ($subscribers->isEmpty()) {
            $this->info('No subscribers.');
            return self::SUCCESS;
        }

        $siteName = config('app.name');
        $lines = ["Top 5 flourishing startups this week:\n"];
        foreach ($startups as $i => $s) {
            $lines[] = ($i + 1) . '. ' . $s->name . ' - ' . url('/startups/' . $s->slug);
        }
        $baseBody = implode("\n", $lines);

        foreach ($subscribers as $sub) {
            $unsubscribeUrl = URL::signedRoute('newsletter.unsubscribe', ['email' => $sub->email]);
            $body = $baseBody . "\n\n— " . $siteName . "\n\nTo unsubscribe: " . $unsubscribeUrl;
            try {
                Mail::raw($body, function ($m) use ($sub, $siteName) {
                    $m->to($sub->email)->subject('Top 5 flourishing startups - ' . $siteName);
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Eden newsletter send failed.', [
                    'email' => $sub->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Cache::put('eden_last_newsletter', now()->toDateTimeString(), 86400 * 30);
        $this->info('Newsletter sent to ' . $subscribers->count() . ' subscribers.');
        return self::SUCCESS;
    }
}
