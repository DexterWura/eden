<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Eden – Application Entry Point (Flippa-style: root entry, Laravel in core/)
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL & ~E_DEPRECATED);
define('LARAVEL_START', microtime(true));

$root = __DIR__;
$core = $root . DIRECTORY_SEPARATOR . 'core';
$autoloader = $core . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// If vendor is missing, try to run composer in core/ (Flippa-style)
if (! file_exists($autoloader)) {
    @set_time_limit(300);
    $phar = $root . DIRECTORY_SEPARATOR . 'composer.phar';

    if (! file_exists($phar) && ! in_array('file_get_contents', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
        $content = @file_get_contents('https://getcomposer.org/composer.phar');
        if ($content !== false && strlen($content) > 1000) {
            @file_put_contents($phar, $content);
        }
    }

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
        if (file_exists($autoloader)) {
            header('Location: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'));
            exit;
        }
    }

    header('Content-Type: text/html; charset=utf-8');
    header('HTTP/1.1 200 OK');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup required – Eden</title><style>body{font-family:system-ui,sans-serif;max-width:560px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}h1{color:#6CAA64;}a{color:#6CAA64;}code{background:#eee;padding:.2em .4em;border-radius:4px;}p{line-height:1.5;}</style></head><body><h1>Setup required</h1><p>Run in the <code>core</code> directory: <code>composer install --no-dev</code> (or put <code>composer.phar</code> in the project root and reload). Then <a href="/install">open the installer</a>.</p></body></html>';
    exit;
}

// Maintenance mode
$maintenance = $core . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'maintenance.php';
if (file_exists($maintenance)) {
    require $maintenance;
}

require $autoloader;

$configCache = $core . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.php';
if (file_exists($configCache)) {
    @unlink($configCache);
}

$app = require_once $core . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Request::capture())->send();
$kernel->terminate($request, $response);
