<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (! file_exists($main = __DIR__.'/../vendor/autoload.php')) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    $installUrl = '/install';
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup required – Eden</title><style>body{font-family:system-ui,sans-serif;max-width:560px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}h1{color:#6CAA64;}a{color:#6CAA64;}code{background:#eee;padding:.2em .4em;border-radius:4px;}p{line-height:1.5;}ul{margin:.5rem 0;}</style></head><body><h1>Setup required</h1><p>Eden needs its dependencies installed on the server. Do one of the following:</p><ul><li><strong>SSH:</strong> In the project root run <code>composer install --no-dev</code></li><li><strong>cPanel Git:</strong> Run a new deploy so the deploy script can run <code>composer install</code></li><li><strong>Other hosts:</strong> Use your control panel or run <code>composer install</code> where the code lives</li></ul><p>After that, <a href="' . htmlspecialchars($installUrl) . '">open the installer</a> to finish setup (database, admin account, etc.).</p></body></html>';
    exit;
}

require $main;

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
