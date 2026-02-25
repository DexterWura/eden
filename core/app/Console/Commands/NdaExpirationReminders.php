<?php

namespace App\Console\Commands;

use App\Jobs\SendNdaExpirationReminders;
use Illuminate\Console\Command;

class NdaExpirationReminders extends Command
{
    protected $signature = 'nda:expiration-reminders';

    protected $description = 'Send NDA expiration reminders (7-day and 1-day) to users';

    public function handle(): int
    {
        (new SendNdaExpirationReminders())->handle();
        $this->info('NDA expiration reminders completed.');
        return 0;
    }
}
