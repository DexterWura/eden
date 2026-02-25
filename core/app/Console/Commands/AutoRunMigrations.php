<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AutoRunMigrations extends Command
{
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
    protected $description = 'Automatically detect and run pending or modified migrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Check if migrations table exists
            if (!Schema::hasTable('migrations')) {
                $this->info('Creating migrations table...');
                Artisan::call('migrate:install', [], $this->getOutput());
            }

            // Get pending migrations
            $pending = $this->getPendingMigrations();
            $modified = $this->getModifiedMigrations();

            if (empty($pending) && empty($modified)) {
                $this->info('No pending or modified migrations found.');
                return 0;
            }

            if ($this->option('check-only')) {
                $this->info('Pending migrations: ' . count($pending));
                $this->info('Modified migrations: ' . count($modified));
                return 0;
            }

            // Check production environment
            if (app()->environment('production') && !$this->option('force')) {
                $this->error('Cannot auto-run migrations in production without --force flag');
                return 1;
            }

            $this->info('Running migrations...');
            
            // Run migrations
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ], $this->getOutput());

            if ($exitCode === 0) {
                $this->info('Migrations completed successfully.');
                
                // Update tracking
                $this->updateMigrationTracking();
                
                Log::info('Auto migrations completed', [
                    'pending_count' => count($pending),
                    'modified_count' => count($modified),
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

    /**
     * Get pending migrations
     */
    private function getPendingMigrations()
    {
        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');
        
        if (!Schema::hasTable('migrations')) {
            return array_map('basename', $files);
        }

        $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
        
        $pending = [];
        foreach ($files as $file) {
            $migrationName = str_replace('.php', '', basename($file));
            if (!in_array($migrationName, $ranMigrations)) {
                $pending[] = $migrationName;
            }
        }
        
        return $pending;
    }

    /**
     * Get modified migrations
     */
    private function getModifiedMigrations()
    {
        if (!Schema::hasTable('migration_tracking')) {
            return [];
        }

        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');
        
        $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
        $tracking = \App\Models\MigrationTracking::whereIn('migration_name', $ranMigrations)->get()->keyBy('migration_name');
        
        $modified = [];
        foreach ($files as $file) {
            $migrationName = str_replace('.php', '', basename($file));
            
            if (in_array($migrationName, $ranMigrations)) {
                $trackingRecord = $tracking->get($migrationName);
                
                if ($trackingRecord) {
                    $currentHash = hash_file('sha256', $file);
                    if ($trackingRecord->file_hash !== $currentHash) {
                        $modified[] = $migrationName;
                    }
                }
            }
        }
        
        return $modified;
    }

    /**
     * Update migration tracking
     */
    private function updateMigrationTracking()
    {
        if (!Schema::hasTable('migration_tracking')) {
            return;
        }

        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');
        $dbStatus = DB::table('migrations')->pluck('batch', 'migration')->toArray();
        
        foreach ($files as $file) {
            $migrationName = str_replace('.php', '', basename($file));
            $isInDb = isset($dbStatus[$migrationName]);
            
            $tracking = \App\Models\MigrationTracking::firstOrNew(['migration_name' => $migrationName]);
            
            $oldHash = $tracking->file_hash;
            $newHash = hash_file('sha256', $file);
            
            if ($isInDb && $oldHash && $oldHash !== $newHash) {
                $tracking->status = 'modified';
            } elseif ($isInDb) {
                $tracking->status = 'ran';
            } else {
                $tracking->status = 'pending';
            }
            
            $tracking->file_hash = $newHash;
            $tracking->file_size = filesize($file);
            $tracking->file_modified_at = date('Y-m-d H:i:s', filemtime($file));
            
            if ($isInDb && !$tracking->last_run_at) {
                $tracking->last_run_at = now();
            }
            
            if ($isInDb) {
                $tracking->run_count = ($tracking->run_count ?? 0) + 1;
            }
            
            $tracking->save();
        }
    }

    /**
     * Get output handler
     */
    public function getOutput()
    {
        return new \Symfony\Component\Console\Output\BufferedOutput();
    }
}

