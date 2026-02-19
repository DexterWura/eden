<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class MigrationController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
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
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return redirect()->route('admin.migrations.index')->with('success', 'Migrations run successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.migrations.index')->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }
}
