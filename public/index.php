<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (! file_exists($main = __DIR__.'/../vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup required</title><style>body{font-family:system-ui,sans-serif;max-width:560px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}h1{color:#6CAA64;}a{color:#6CAA64;}code{background:#eee;padding:.2em .4em;border-radius:4px;}</style></head><body><h1>Setup required</h1><p>Run <code>composer install</code> in the project root, then reload this page or open <a href="' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') . 'install">/install</a> to use the web installer.</p></body></html>';
    exit;
}

require $main;

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
