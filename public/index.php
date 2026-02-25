<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (! file_exists($main = __DIR__.'/../vendor/autoload.php')) {
    $root = __DIR__ . '/..';
    $phar = $root . '/composer.phar';
    if (file_exists($phar) && ! in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))), true)) {
        @set_time_limit(300);
        @chdir($root);
        $cmd = 'php ' . escapeshellarg($phar) . ' install --no-dev --no-interaction 2>&1';
        @exec($cmd, $out, $code);
        if ($code === 0 && file_exists($root . '/vendor/autoload.php')) {
            header('Location: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'));
            exit;
        }
    }
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    $installUrl = '/install';
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup required – Eden</title><style>body{font-family:system-ui,sans-serif;max-width:560px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}h1{color:#6CAA64;}a{color:#6CAA64;}code{background:#eee;padding:.2em .4em;border-radius:4px;}p{line-height:1.5;}ul{margin:.5rem 0;}</style></head><body><h1>Setup required</h1><p>Eden needs its dependencies. If <code>composer.phar</code> is in the project root, reload this page to run it automatically. Otherwise run <code>php composer.phar install --no-dev</code> (or <code>composer install</code>) in the project root, then <a href="' . htmlspecialchars($installUrl) . '">open the installer</a>.</p></body></html>';
    exit;
}

require $main;

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
