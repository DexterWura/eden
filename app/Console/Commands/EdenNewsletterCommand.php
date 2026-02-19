<?php

namespace App\Console\Commands;

use App\Models\NewsletterSubscriber;
use App\Models\Startup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

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
        $body = implode("\n", $lines) . "\n\n— " . $siteName;

        foreach ($subscribers as $sub) {
            try {
                Mail::raw($body, function ($m) use ($sub, $siteName) {
                    $m->to($sub->email)->subject('Top 5 flourishing startups - ' . $siteName);
                });
            } catch (\Throwable $e) {
                // skip
            }
        }

        Cache::put('eden_last_newsletter', now()->toDateTimeString(), 86400 * 30);
        $this->info('Newsletter sent to ' . $subscribers->count() . ' subscribers.');
        return self::SUCCESS;
    }
}
