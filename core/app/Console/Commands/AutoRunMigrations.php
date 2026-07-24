<?php

namespace App\Console\Commands;

use App\Services\MigrationDriftService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AutoRunMigrations extends Command
{
    public function __construct(
        private MigrationDriftService $migrationDriftService
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:auto 
                            {--check-only : Only check for pending migrations without running}
                            {--force : Force migration in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect migration drift and run pending forward migrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $beforeStates = $this->migrationDriftService->states();
            $pending = $beforeStates->where('state', 'pending');
            $drifted = $beforeStates->whereIn('state', ['modified', 'missing_file', 'untracked']);

            if ($pending->isEmpty() && $drifted->isEmpty()) {
                $this->info('No pending migrations or checksum drift found.');
                return 0;
            }

            if ($this->option('check-only')) {
                $this->info('Pending migrations: ' . $pending->count());
                $this->info('Drifted migrations: ' . $drifted->count());
                return $drifted->isEmpty() ? 0 : 2;
            }

            // Check production environment
            if (app()->environment('production') && !$this->option('force')) {
                $this->error('Cannot auto-run migrations in production without --force flag');
                return 1;
            }

            if ($pending->isEmpty()) {
                $this->error('Applied migration drift detected. Restore the original file or create a new repair migration.');
                return 2;
            }

            $this->info('Running pending forward migrations...');
            
            // Run migrations
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ], new \Symfony\Component\Console\Output\BufferedOutput());

            if ($exitCode === 0) {
                $this->info('Migrations completed successfully.');
                
                $this->migrationDriftService->recordApplied($beforeStates);
                
                Log::info('Auto migrations completed', [
                    'pending_count' => $pending->count(),
                    'drifted_count' => $drifted->count(),
                ]);
                
                return 0;
            } else {
                $this->error('Migration failed with exit code: ' . $exitCode);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Auto migration error: ' . $e->getMessage());
            return 1;
        }
    }
}

