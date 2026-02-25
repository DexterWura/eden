<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MigrationTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class MigrationController extends Controller
{
    /**
     * Display migration status page
     */
    public function index()
    {
        $pageTitle = 'Database Migrations';
        
        // Get all migration files
        $migrationFiles = $this->getMigrationFiles();
        
        // Get migration status from database
        $migrationStatus = $this->getMigrationStatus();
        
        // Get pending migrations
        $pendingMigrations = $this->getPendingMigrations();
        
        // Get modified migrations
        $modifiedMigrations = $this->getModifiedMigrations();
        
        // Get ran migrations
        $ranMigrations = $this->getRanMigrations();
        
        // Check if migrations table exists
        $migrationsTableExists = Schema::hasTable('migrations');
        
        return view('admin.migration.index', compact(
            'pageTitle',
            'migrationFiles',
            'migrationStatus',
            'pendingMigrations',
            'modifiedMigrations',
            'ranMigrations',
            'migrationsTableExists'
        ));
    }

    /**
     * Get status of all migrations (API endpoint)
     */
    public function status()
    {
        try {
            $migrations = $this->getAllMigrationsWithStatus();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'migrations' => $migrations,
                    'pending_count' => count($this->getPendingMigrations()),
                    'modified_count' => count($this->getModifiedMigrations()),
                    'ran_count' => count($this->getRanMigrations()),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Migration status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get migration status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a raw SQL backup of the current database (MySQL/MariaDB via mysqldump)
     */
    public function downloadSql(Request $request)
    {
        try {
            set_time_limit(0);
            ignore_user_abort(true);

            $defaultConnection = config('database.default');
            $conn = config("database.connections.{$defaultConnection}");

            if (!is_array($conn) || ($conn['driver'] ?? null) !== 'mysql') {
                $notify[] = ['error', 'SQL download is currently supported only for MySQL/MariaDB databases.'];
                return back()->withNotify($notify);
            }

            $database = $conn['database'] ?? null;
            $username = $conn['username'] ?? null;
            $password = $conn['password'] ?? null;
            $host = $conn['host'] ?? '127.0.0.1';
            $port = (string)($conn['port'] ?? '3306');

            if (!$database || !$username) {
                $notify[] = ['error', 'Database credentials are missing in configuration.'];
                return back()->withNotify($notify);
            }

            $backupDir = storage_path('app/backups/sql');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $timestamp = now()->format('Ymd-His');
            $dumpFile = $backupDir . DIRECTORY_SEPARATOR . "db-backup-{$database}-{$timestamp}.sql";
            $downloadName = "db-backup-{$database}-{$timestamp}.sql";

            // Create a temporary MySQL client credentials file to avoid exposing password in process args
            $credFile = $backupDir . DIRECTORY_SEPARATOR . ".mysqldump-{$timestamp}-" . Str::random(8) . ".cnf";
            $credContents = "[client]\n";
            $credContents .= "user={$username}\n";
            if ($password !== null && $password !== '') {
                $credContents .= "password={$password}\n";
            }
            $credContents .= "host={$host}\n";
            $credContents .= "port={$port}\n";
            File::put($credFile, $credContents);

            $cmd = [
                'mysqldump',
                "--defaults-extra-file={$credFile}",
                '--single-transaction',
                '--quick',
                '--routines',
                '--events',
                '--triggers',
                '--add-drop-table',
                '--databases',
                $database,
            ];

            $process = new Process($cmd);
            $process->setTimeout(null);

            $process->run(function ($type, $buffer) use ($dumpFile) {
                // Stream mysqldump output directly into file
                File::append($dumpFile, $buffer);
            });

            // Always remove the credentials file
            File::delete($credFile);

            if (!$process->isSuccessful()) {
                Log::error('mysqldump failed', [
                    'exit_code' => $process->getExitCode(),
                    'error' => $process->getErrorOutput(),
                ]);
                if (File::exists($dumpFile)) {
                    File::delete($dumpFile);
                }
                $notify[] = ['error', 'Failed to generate SQL backup. Ensure `mysqldump` is installed and available on the server PATH.'];
                return back()->withNotify($notify);
            }

            if (!File::exists($dumpFile) || File::size($dumpFile) === 0) {
                if (File::exists($dumpFile)) {
                    File::delete($dumpFile);
                }
                $notify[] = ['error', 'SQL backup was generated but the file is empty. Ensure the database user has access and `mysqldump` works on the server.'];
                return back()->withNotify($notify);
            }

            return response()->download($dumpFile, $downloadName, [
                'Content-Type' => 'application/sql',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('SQL backup download failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $notify[] = ['error', 'Failed to generate SQL backup: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    /**
     * Run pending migrations
     */
    public function run(Request $request)
    {
        try {
            // Security check - only allow in non-production or with force flag
            if (app()->environment('production') && !$request->has('force')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Migrations cannot be run in production without force flag'
                ], 403);
            }

            // Get pending migrations
            $pending = $this->getPendingMigrations();
            $modified = $this->getModifiedMigrations();
            
            if (empty($pending) && empty($modified)) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'No pending migrations to run'
                ]);
            }

            // Check if migrations table exists
            if (!Schema::hasTable('migrations')) {
                // Create migrations table first
                $installBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
                Artisan::call('migrate:install', [], $installBuffer);
            }

            // Run migrations
            $output = [];
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput());
            
            $output = $outputBuffer->fetch();

            // Update tracking for ran migrations
            $this->updateMigrationTracking();

            // Log the migration run
            Log::info('Migrations run via UI', [
                'admin_id' => auth()->id(),
                'pending_count' => count($pending),
                'modified_count' => count($modified),
                'output' => $output
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Migrations run successfully',
                'data' => [
                    'output' => $output,
                    'pending_count' => count($pending),
                    'modified_count' => count($modified),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Migration run error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to run migrations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run a specific migration
     */
    public function runSpecific(Request $request, $migrationName)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'force' => 'nullable|boolean',
                'confirm' => 'required|accepted',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please confirm you want to run this migration',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (app()->environment('production') && !$request->boolean('force')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot run specific migration in production without force flag'
                ], 403);
            }

            $migrationFile = $this->findMigrationFile($migrationName);
            
            if (!$migrationFile) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Migration file not found'
                ], 404);
            }

            // Extract path from full path
            $migrationPath = database_path('migrations');
            $relativePath = str_replace($migrationPath . DIRECTORY_SEPARATOR, '', $migrationFile);

            // Run specific migration
            $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
            $exitCode = Artisan::call('migrate', [
                '--path' => $relativePath,
                '--force' => true,
            ], $outputBuffer);
            
            $output = $outputBuffer->fetch();
            
            if ($exitCode !== 0) {
                throw new \Exception('Migration command failed with exit code: ' . $exitCode);
            }

            // Update tracking for this migration
            $this->updateSingleMigrationTracking($migrationName);

            Log::info('Specific migration run via UI', [
                'admin_id' => auth()->id(),
                'migration' => $migrationName,
                'output' => $output
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Migration run successfully',
                'data' => ['output' => $output]
            ]);

        } catch (\Exception $e) {
            Log::error('Specific migration run error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to run migration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rollback last batch of migrations
     */
    public function rollback(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'force' => 'nullable|boolean',
                'steps' => 'nullable|integer|min:1|max:10',
                'confirm' => 'required|accepted',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please confirm you want to rollback migrations',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (app()->environment('production') && !$request->boolean('force')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot rollback in production without force flag. This is a safety measure.'
                ], 403);
            }

            $steps = $request->get('steps', 1);

            $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
            $exitCode = Artisan::call('migrate:rollback', [
                '--step' => $steps,
                '--force' => true,
            ], $outputBuffer);
            
            $output = $outputBuffer->fetch();
            
            if ($exitCode !== 0) {
                throw new \Exception('Rollback command failed with exit code: ' . $exitCode);
            }

            // Update tracking after rollback
            $this->updateMigrationTracking();

            Log::info('Migrations rolled back via UI', [
                'admin_id' => auth()->id(),
                'steps' => $steps,
                'output' => $output
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Migrations rolled back successfully',
                'data' => ['output' => $output]
            ]);

        } catch (\Exception $e) {
            Log::error('Migration rollback error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to rollback migrations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh migration tracking (detect modified migrations)
     */
    public function refresh()
    {
        try {
            $this->updateMigrationTracking();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Migration tracking refreshed',
                'data' => [
                    'pending' => count($this->getPendingMigrations()),
                    'modified' => count($this->getModifiedMigrations()),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Migration refresh error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh migration tracking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all migration files
     */
    private function getMigrationFiles()
    {
        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');
        
        $migrations = [];
        foreach ($files as $file) {
            $fileName = basename($file);
            $migrations[] = [
                'name' => $fileName,
                'path' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'hash' => hash_file('sha256', $file),
            ];
        }
        
        // Sort by filename (which includes timestamp)
        usort($migrations, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $migrations;
    }

    /**
     * Get migration status from database
     */
    private function getMigrationStatus()
    {
        if (!Schema::hasTable('migrations')) {
            return [];
        }

        return DB::table('migrations')
            ->orderBy('batch')
            ->orderBy('migration')
            ->get()
            ->pluck('batch', 'migration')
            ->toArray();
    }

    /**
     * Get all migrations with their status
     */
    private function getAllMigrationsWithStatus()
    {
        $files = $this->getMigrationFiles();
        $dbStatus = $this->getMigrationStatus();
        $tracking = MigrationTracking::all()->keyBy('migration_name');
        
        $migrations = [];
        foreach ($files as $file) {
            $migrationName = $this->getMigrationNameFromFile($file['name']);
            $isInDb = isset($dbStatus[$migrationName]);
            $trackingRecord = $tracking->get($migrationName);
            
            // Check if file was modified
            $isModified = false;
            if ($trackingRecord && $isInDb) {
                $currentHash = $file['hash'];
                if ($trackingRecord->file_hash !== $currentHash) {
                    $isModified = true;
                }
            }
            
            $migrations[] = [
                'name' => $file['name'],
                'migration_name' => $migrationName,
                'status' => $isInDb ? 'ran' : 'pending',
                'is_modified' => $isModified,
                'batch' => $isInDb ? $dbStatus[$migrationName] : null,
                'file_hash' => $file['hash'],
                'file_size' => $file['size'],
                'modified_at' => date('Y-m-d H:i:s', $file['modified']),
            ];
        }
        
        return $migrations;
    }

    /**
     * Get pending migrations
     */
    private function getPendingMigrations()
    {
        $files = $this->getMigrationFiles();
        $dbStatus = $this->getMigrationStatus();
        
        $pending = [];
        foreach ($files as $file) {
            $migrationName = $this->getMigrationNameFromFile($file['name']);
            if (!isset($dbStatus[$migrationName])) {
                $pending[] = $file;
            }
        }
        
        return $pending;
    }

    /**
     * Get modified migrations
     */
    private function getModifiedMigrations()
    {
        $files = $this->getMigrationFiles();
        $dbStatus = $this->getMigrationStatus();
        
        // Check if tracking table exists
        if (!Schema::hasTable('migration_tracking')) {
            return [];
        }
        
        try {
            $tracking = MigrationTracking::all()->keyBy('migration_name');
        } catch (\Exception $e) {
            // Table might not exist yet, return empty array
            return [];
        }
        
        $modified = [];
        foreach ($files as $file) {
            $migrationName = $this->getMigrationNameFromFile($file['name']);
            
            // Only check migrations that have been run
            if (isset($dbStatus[$migrationName])) {
                $trackingRecord = $tracking->get($migrationName);
                
                if ($trackingRecord) {
                    $currentHash = $file['hash'];
                    if ($trackingRecord->file_hash !== $currentHash) {
                        $modified[] = [
                            'file' => $file,
                            'migration_name' => $migrationName,
                            'old_hash' => $trackingRecord->file_hash,
                            'new_hash' => $currentHash,
                        ];
                    }
                }
            }
        }
        
        return $modified;
    }

    /**
     * Get ran migrations
     */
    private function getRanMigrations()
    {
        $files = $this->getMigrationFiles();
        $dbStatus = $this->getMigrationStatus();
        
        $ran = [];
        foreach ($files as $file) {
            $migrationName = $this->getMigrationNameFromFile($file['name']);
            if (isset($dbStatus[$migrationName])) {
                $ran[] = [
                    'file' => $file,
                    'migration_name' => $migrationName,
                    'batch' => $dbStatus[$migrationName],
                ];
            }
        }
        
        return $ran;
    }

    /**
     * Update migration tracking
     */
    private function updateMigrationTracking()
    {
        // Check if tracking table exists
        if (!Schema::hasTable('migration_tracking')) {
            return;
        }
        
        $files = $this->getMigrationFiles();
        $dbStatus = $this->getMigrationStatus();
        
        foreach ($files as $file) {
            $migrationName = $this->getMigrationNameFromFile($file['name']);
            $isInDb = isset($dbStatus[$migrationName]);
            
            $tracking = MigrationTracking::firstOrNew(['migration_name' => $migrationName]);
            
            $oldHash = $tracking->file_hash;
            $newHash = $file['hash'];
            
            // Check if file was modified
            if ($isInDb && $oldHash && $oldHash !== $newHash) {
                $tracking->status = 'modified';
            } elseif ($isInDb) {
                $tracking->status = 'ran';
            } else {
                $tracking->status = 'pending';
            }
            
            $tracking->file_hash = $newHash;
            $tracking->file_size = $file['size'];
            $tracking->file_modified_at = date('Y-m-d H:i:s', $file['modified']);
            
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
     * Update tracking for a single migration
     */
    private function updateSingleMigrationTracking($migrationName)
    {
        // Check if tracking table exists
        if (!Schema::hasTable('migration_tracking')) {
            return;
        }
        
        $file = $this->findMigrationFile($migrationName);
        
        if (!$file) {
            return;
        }
        
        $fileInfo = [
            'name' => basename($file),
            'path' => $file,
            'size' => filesize($file),
            'modified' => filemtime($file),
            'hash' => hash_file('sha256', $file),
        ];
        
        $dbStatus = $this->getMigrationStatus();
        $isInDb = isset($dbStatus[$migrationName]);
        
        $tracking = MigrationTracking::firstOrNew(['migration_name' => $migrationName]);
        $tracking->file_hash = $fileInfo['hash'];
        $tracking->file_size = $fileInfo['size'];
        $tracking->file_modified_at = date('Y-m-d H:i:s', $fileInfo['modified']);
        $tracking->status = $isInDb ? 'ran' : 'pending';
        $tracking->last_run_at = now();
        $tracking->run_count = ($tracking->run_count ?? 0) + 1;
        $tracking->save();
    }

    /**
     * Get migration name from filename
     */
    private function getMigrationNameFromFile($fileName)
    {
        // Remove .php extension
        return str_replace('.php', '', $fileName);
    }

    /**
     * Find migration file by name
     */
    private function findMigrationFile($migrationName)
    {
        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');
        
        foreach ($files as $file) {
            $fileName = basename($file);
            $name = $this->getMigrationNameFromFile($fileName);
            
            if ($name === $migrationName || str_contains($fileName, $migrationName)) {
                return $file;
            }
        }
        
        return null;
    }

    /**
     * Get output handler for Artisan commands
     */
    private function getOutput()
    {
        return new \Symfony\Component\Console\Output\BufferedOutput();
    }
}

