<?php

namespace App\Console\Commands;

use App\Jobs\ProcessNdaExpiration;
use Illuminate\Console\Command;

class ProcessExpiredNdas extends Command
{
    protected $signature = 'nda:process-expired';

    protected $description = 'Process expired NDAs and notify users and sellers';

    public function handle(): int
    {
        (new ProcessNdaExpiration())->handle();
        $this->info('Expired NDAs processed.');
        return 0;
    }
}
