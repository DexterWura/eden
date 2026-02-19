<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class InstallController extends Controller
{
    public function __construct()
    {
        if (config('eden.installed') || env('EDEN_INSTALLED')) {
            abort(404);
        }
    }

    public function index()
    {
        $requirements = [
            'php' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'json' => extension_loaded('json'),
            'fileinfo' => extension_loaded('fileinfo'),
        ];
        $allOk = ! in_array(false, $requirements, true);

        return view('install.index', compact('requirements', 'allOk'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'db_host' => 'required|string|max:255',
            'db_database' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string',
            'timezone' => 'required|string|max:50',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => ['required', 'confirmed', Password::defaults()],
            'site_logo_url' => 'nullable|url|max:500',
        ]);

        $envContent = $this->buildEnv($validated);
        $envPath = base_path('.env');
        if (! file_exists($envPath) && file_exists(base_path('.env.example'))) {
            copy(base_path('.env.example'), $envPath);
        }
        $this->writeEnv($envPath, $validated);

        try {
            Artisan::call('key:generate', ['--force' => true]);
        } catch (\Throwable $e) {
            // ignore if key already set
        }

        config(['database.connections.mysql.host' => $validated['db_host']]);
        config(['database.connections.mysql.database' => $validated['db_database']]);
        config(['database.connections.mysql.username' => $validated['db_username']]);
        config(['database.connections.mysql.password' => $validated['db_password'] ?? '']);

        try {
            DB::connection()->getPdo();
            DB::connection()->getDatabaseName();
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['db' => 'Could not connect to database: ' . $e->getMessage()]);
        }

        Artisan::call('migrate', ['--force' => true]);

        $user = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
            'points' => 0,
        ]);

        $setting = app(SettingService::class);
        $setting->set('site_name', $validated['site_name']);
        $setting->set('app_url', $validated['app_url']);
        $setting->set('timezone', $validated['timezone']);
        $setting->set('theme', config('themes.default', 'basic'));
        if (! empty($validated['site_logo_url'] ?? null)) {
            $setting->set('site_logo', $validated['site_logo_url']);
        }

        $this->setInstalled();

        return redirect()->route('login')->with('success', 'Eden is installed. Log in with your admin account.');
    }

    protected function buildEnv(array $v): string
    {
        $key = config('app.key') ?: 'base64:' . base64_encode(Str::random(32));
        return implode("\n", [
            'APP_NAME="' . addslashes($v['site_name']) . '"',
            'APP_ENV=production',
            'APP_KEY=' . $key,
            'APP_DEBUG=false',
            'APP_TIMEZONE=' . $v['timezone'],
            'APP_URL=' . $v['app_url'],
            'EDEN_INSTALLED=true',
            'DB_CONNECTION=mysql',
            'DB_HOST=' . $v['db_host'],
            'DB_PORT=3306',
            'DB_DATABASE=' . $v['db_database'],
            'DB_USERNAME=' . $v['db_username'],
            'DB_PASSWORD=' . ($v['db_password'] ?? ''),
        ]);
    }

    protected function writeEnv(string $path, array $v): void
    {
        $lines = [];
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        }
        $replace = [
            'APP_NAME' => $v['site_name'],
            'APP_URL' => $v['app_url'],
            'APP_TIMEZONE' => $v['timezone'],
            'EDEN_INSTALLED' => 'true',
            'DB_HOST' => $v['db_host'],
            'DB_DATABASE' => $v['db_database'],
            'DB_USERNAME' => $v['db_username'],
            'DB_PASSWORD' => $v['db_password'] ?? '',
        ];
        $out = [];
        $done = [];
        foreach ($lines as $line) {
            foreach ($replace as $key => $val) {
                if (str_starts_with(trim($line), $key . '=')) {
                    $out[] = $key . '=' . (in_array($key, ['DB_PASSWORD','APP_KEY'], true) ? $val : '"' . addslashes($val) . '"');
                    $done[$key] = true;
                    continue 2;
                }
            }
            $out[] = $line;
        }
        foreach ($replace as $key => $val) {
            if (empty($done[$key])) {
                $out[] = $key . '=' . (in_array($key, ['DB_PASSWORD','APP_KEY'], true) ? $val : '"' . addslashes($val) . '"');
            }
        }
        if (! str_contains(implode("\n", $out), 'EDEN_INSTALLED')) {
            $out[] = 'EDEN_INSTALLED=true';
        }
        file_put_contents($path, implode("\n", $out));
    }

    protected function setInstalled(): void
    {
        $path = config_path('eden.php');
        $c = file_get_contents($path);
        $c = preg_replace("/'installed'\s*=>\s*env\s*\([^)]+\)/", "'installed' => true", $c);
        file_put_contents($path, $c);
        @file_put_contents(storage_path('installed.lock'), date('c'));
    }
}
