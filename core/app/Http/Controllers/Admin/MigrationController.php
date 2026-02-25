<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    public function index()
    {
        Artisan::call('migrate:status');
        $output = Artisan::output();
        $pending = [];
        foreach (explode("\n", $output) as $line) {
            if (str_contains($line, 'Pending')) {
                if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_.+/', trim($line), $m)) {
                    $pending[] = trim($line);
                }
            }
        }
        $hasPending = count($pending) > 0 || str_contains($output, 'Pending');
        return view('admin.migrations.index', compact('output', 'pending', 'hasPending'));
    }

    public function run()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return redirect()->route('admin.migrations.index')->with('success', 'Migrations run successfully.');
        } catch (\Throwable $e) {
            Log::error('Migration failed', ['exception' => $e]);
            $message = app()->environment('local')
                ? 'Migration failed: ' . $e->getMessage()
                : 'Migration failed. Check logs.';

            return redirect()->route('admin.migrations.index')->with('error', $message);
        }
    }
}
