<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (! file_exists($main = __DIR__.'/../vendor/autoload.php')) {
    @set_time_limit(300);
    $core = realpath(__DIR__ . '/..');
    $root = realpath($core . DIRECTORY_SEPARATOR . '..');
    $sep = DIRECTORY_SEPARATOR;
    $phar = ($root ? $root . $sep . 'composer.phar' : $core . $sep . '..' . $sep . 'composer.phar');
    $vendorAutoload = $core . $sep . 'vendor' . $sep . 'autoload.php';

    // Flippa-style: try to get composer.phar if missing (download from getcomposer.org)
    if (! file_exists($phar) && ! in_array('file_get_contents', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
        $content = @file_get_contents('https://getcomposer.org/composer.phar');
        if ($content !== false && strlen($content) > 1000) {
            @file_put_contents($phar, $content);
        }
    }

    // Flippa-style composer detection: system composer, then composer.phar in root, then php composer.phar
    $composerPath = null;
    if (function_exists('shell_exec') && ! in_array('shell_exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
        $check = @shell_exec('composer --version 2>&1');
        if ($check && strpos($check, 'Composer') !== false) {
            $composerPath = 'composer';
        }
        if (! $composerPath && file_exists($phar)) {
            $check = @shell_exec('php ' . escapeshellarg($phar) . ' --version 2>&1');
            if ($check && strpos($check, 'Composer') !== false) {
                $composerPath = 'php ' . escapeshellarg($phar);
            }
        }
        if (! $composerPath) {
            $check = @shell_exec('php composer.phar --version 2>&1');
            if ($check && strpos($check, 'Composer') !== false) {
                $composerPath = 'php composer.phar';
            }
        }
    }

    if ($composerPath) {
        $command = 'cd ' . escapeshellarg($core) . ' && ' . $composerPath . ' install --no-dev --no-interaction --optimize-autoloader 2>&1';
        if (file_exists($phar) && strpos($composerPath, 'composer.phar') !== false) {
            $command = 'cd ' . escapeshellarg($core) . ' && php ' . escapeshellarg($phar) . ' install --no-dev --no-interaction --optimize-autoloader 2>&1';
        }
        @shell_exec($command);
        if (file_exists($vendorAutoload)) {
            header('Location: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'));
            exit;
        }
    }

    // Fallback: run via exec/passthru/proc_open with phar path (same as before)
    if (file_exists($phar)) {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        $phpCandidates = array_filter([
            defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : null,
            'php',
            '/usr/bin/php',
            '/usr/local/bin/php',
        ]);
        $code = -1;
        $tryRun = function ($cmd) use ($core, $disabled, &$code) {
            $code = -1;
            if (! in_array('exec', $disabled, true)) {
                @chdir($core);
                @exec($cmd, $out, $code);
                return true;
            }
            if (! in_array('passthru', $disabled, true)) {
                @chdir($core);
                ob_start();
                @passthru($cmd, $code);
                ob_end_clean();
                return true;
            }
            if (! in_array('proc_open', $disabled, true)) {
                $p = @proc_open($cmd, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $core);
                if (is_resource($p)) {
                    @fclose($p[0]);
                    @stream_get_contents($p[1]);
                    @stream_get_contents($p[2]);
                    $code = @proc_close($p);
                    return true;
                }
            }
            return false;
        };
        foreach ($phpCandidates as $php) {
            $cmd = 'cd ' . escapeshellarg($core) . ' && ' . $php . ' ' . escapeshellarg($phar) . ' install --no-dev --no-interaction --optimize-autoloader 2>&1';
            if ($tryRun($cmd) && file_exists($vendorAutoload)) {
                header('Location: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'));
                exit;
            }
        }
    }

    header('Content-Type: text/html; charset=utf-8');
    header('HTTP/1.1 200 OK');
    $installUrl = '/install';
    $refresh = (file_exists($phar) || $composerPath) ? '<p><a href="' . htmlspecialchars($installUrl) . '">Reload</a> to try again.</p>' : '';
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup required – Eden</title><style>body{font-family:system-ui,sans-serif;max-width:560px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}h1{color:#6CAA64;}a{color:#6CAA64;}code{background:#eee;padding:.2em .4em;border-radius:4px;}p{line-height:1.5;}ul{margin:.5rem 0;}</style></head><body><h1>Setup required</h1><p>Eden needs its dependencies. If <code>composer.phar</code> is missing, this page tries to download it; then it runs <code>composer install</code> (same logic as the Flippa clone).</p><p>Run in the <strong>core</strong> directory: <code>cd core && php ../composer.phar install --no-dev</code> (or <code>cd core && composer install</code>). Then <a href="' . htmlspecialchars($installUrl) . '">open the installer</a>.</p>' . $refresh . '</body></html>';
    exit;
}

require $main;

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
