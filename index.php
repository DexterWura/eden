<?php
/**
 * Eden – Laravel application entry point.
 * Document root should be this directory. If .env is missing, redirect to installer.
 */

error_reporting(E_ALL & ~E_DEPRECATED);
define('LARAVEL_START', microtime(true));

$root = __DIR__;
$envPath = $root . DIRECTORY_SEPARATOR . '.env';

if (!file_exists($envPath)) {
    header('Location: /install/');
    exit;
}

$maintenance = $root . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'maintenance.php';
if (file_exists($maintenance)) {
    require $maintenance;
}

$autoloader = $root . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!file_exists($autoloader)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Setup</title></head><body><h1>Setup required</h1><p>Run: <code>cd core && composer install --no-dev</code></p><p>Or copy the <code>core/vendor</code> folder from another machine that has run composer.</p></body></html>';
    exit;
}

require $autoloader;

$configCachePath = $root . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.php';
if (file_exists($configCachePath)) {
    @unlink($configCachePath);
}

try {
    $dotenv = Dotenv\Dotenv::createImmutable($root);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidFileException $e) {
    header('Location: /install/');
    exit;
} catch (Exception $e) {
    if (function_exists('error_log')) {
        error_log('Eden: Failed to load .env: ' . $e->getMessage());
    }
}

$app = require_once $root . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
$app->handleRequest(Illuminate\Http\Request::capture());
