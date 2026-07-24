<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MigrationDriftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class MigrationController extends Controller
{
    public function __construct(
        private MigrationDriftService $migrationDriftService
    ) {
        parent::__construct();
    }

    /**
     * Display migration status page (Eden dashboard layout)
     */
    public function index(Request $request)
    {
        $states = $this->migrationDriftService->states();
        $migrationFilesAll = $states->whereNotNull('file')->pluck('file')->values()->all();
        $migrationStatus = $states->whereNotNull('batch')->pluck('batch', 'name')->all();
        $pendingMigrations = $states->where('state', 'pending')->pluck('file')->values()->all();
        $modifiedMigrations = $states->whereIn('state', ['modified', 'missing_file', 'untracked'])->values()->all();
        $ranMigrations = $states->where('state', 'applied')->values()->all();
        $migrationsTableExists = Schema::hasTable('migrations');

        $page = max(1, (int) $request->get('page', 1));
        $perPage = 10;
        $total = count($migrationFilesAll);
        $slice = array_slice($migrationFilesAll, ($page - 1) * $perPage, $perPage);
        $migrationFiles = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $content = view('eden.migrations', compact(
            'migrationFiles',
            'migrationStatus',
            'pendingMigrations',
            'modifiedMigrations',
            'ranMigrations',
            'migrationsTableExists',
            'states'
        ))->render();

        $runSpecificUrlTemplate = route('admin.migration.run.specific', ['migration' => 'REPLACE_MIGRATION']);
        $scripts = '<script>window.EDEN_MIGRATION=' . json_encode([
            'runUrl' => route('admin.migration.run'),
            'runSpecificUrlTemplate' => $runSpecificUrlTemplate,
            'checkUrl' => route('admin.migration.check'),
            'rollbackUrl' => route('admin.migration.rollback'),
            'csrfToken' => csrf_token(),
            'production' => app()->environment('production'),
        ]) . ';</script>' . "\n" . '<script src="' . asset('js/migration.js') . '"></script>';

        return response()->view('eden.layout-dashboard', [
            'title' => 'Database migrations',
            'sidebar' => 'admin',
            'activeNav' => 'migrations',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All apps</button>',
            'searchPlaceholder' => "Try searching 'apps by category'",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
            'scripts' => $scripts,
        ]);
    }

    /**
     * Get status of all migrations (API endpoint)
     */
    public function status()
    {
        try {
            $migrations = $this->migrationDriftService->states()->values();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'migrations' => $migrations,
                    'pending_count' => $migrations->where('state', 'pending')->count(),
                    'modified_count' => $migrations->whereIn('state', ['modified', 'missing_file', 'untracked'])->count(),
                    'ran_count' => $migrations->where('state', 'applied')->count(),
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
            $validator = Validator::make($request->all(), [
                'confirm' => ['required', 'accepted'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please confirm the forward migration run.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $beforeStates = $this->migrationDriftService->states();
            $pending = $beforeStates->where('state', 'pending');
            if ($pending->isEmpty()) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'No pending migrations to run. Drifted applied files require a new repair migration.'
                ]);
            }

            // Check if migrations table exists
            if (!Schema::hasTable('migrations')) {
                // Create migrations table first
                $installBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
                Artisan::call('migrate:install', [], $installBuffer);
            }

            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput());
            $output = $outputBuffer->fetch();
            if ($exitCode !== 0) {
                throw new \RuntimeException('Migration command failed with exit code ' . $exitCode . '.');
            }
            $this->migrationDriftService->recordApplied($beforeStates);

            // Log the migration run
            Log::info('Migrations run via UI', [
                'admin_id' => auth('admin')->id(),
                'pending_count' => count($pending),
                'output' => $output
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Migrations run successfully',
                'data' => [
                    'output' => $output,
                    'pending_count' => count($pending),
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
                'confirm' => 'required|accepted',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please confirm you want to run this migration',
                    'errors' => $validator->errors()
                ], 422);
            }

            $state = $this->migrationDriftService->state($migrationName);
            if (! $state) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Migration file not found.',
                ], 404);
            }
            if ($state['state'] !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Applied migrations cannot be rerun. Create a new forward repair migration instead.',
                    'repair' => $this->repairGuidance($migrationName),
                ], 409);
            }

            $migrationFile = $state['file']['path'];
            $migrationPath = database_path('migrations');
            $relativePath = str_replace($migrationPath . DIRECTORY_SEPARATOR, '', $migrationFile);
            $artisanPath = 'database/migrations/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            // Run specific migration
            $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput();
            $exitCode = Artisan::call('migrate', [
                '--path' => $artisanPath,
                '--force' => true,
            ], $outputBuffer);
            
            $output = $outputBuffer->fetch();
            
            if ($exitCode !== 0) {
                throw new \Exception('Migration command failed with exit code: ' . $exitCode);
            }

            $this->migrationDriftService->recordApplied(collect([$state]));

            Log::info('Specific migration run via UI', [
                'admin_id' => auth('admin')->id(),
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
            if (app()->environment('production')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Production migrations are forward-only. Create a new repair migration.',
                ], 403);
            }
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

            Log::info('Migrations rolled back via UI', [
                'admin_id' => auth('admin')->id(),
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
    public function check()
    {
        $states = $this->migrationDriftService->states();

        return response()->json([
            'status' => 'success',
            'message' => 'Migration state checked without changing checksum history.',
            'data' => [
                'pending' => $states->where('state', 'pending')->count(),
                'drifted' => $states->whereIn('state', ['modified', 'missing_file', 'untracked'])->count(),
            ],
        ]);
    }

    private function repairGuidance(string $migrationName): array
    {
        $repairName = 'repair_' . preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migrationName);

        return [
            'name' => $repairName,
            'command' => 'php artisan make:migration ' . $repairName,
            'instructions' => 'Put only the additive schema or data correction in the new migration, review its down method, deploy it, then run pending migrations.',
        ];
    }
}

