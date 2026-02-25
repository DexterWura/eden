<?php
/**
 * Eden – entry point. Laravel lives in core/.
 * Document root must be this folder (project root) or core/public.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
define('LARAVEL_START', microtime(true));

$root = __DIR__;
$core = $root . DIRECTORY_SEPARATOR . 'core';
$autoloader = $core . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!file_exists($autoloader)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Setup</title></head><body><h1>Setup required</h1><p>Run: <code>cd core && composer install --no-dev</code></p><p>Then open <a href="/install">/install</a></p></body></html>';
    exit;
}

require $autoloader;
$app = require_once $core . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
