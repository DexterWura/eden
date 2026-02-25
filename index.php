<?php

/**
 * Fallback when the web server document root is the project root instead of public/.
 * Redirects to /public/ so the Laravel app (and installer) can run.
 * For proper setup, configure your server so the document root is the public/ directory.
 */
$publicDir = __DIR__ . '/public';
if (is_dir($publicDir) && file_exists($publicDir . '/index.php')) {
    header('Location: /public/', true, 302);
    exit;
}
header('Content-Type: text/plain; charset=utf-8', true, 500);
echo 'Misconfiguration: point the document root to the public/ directory.';
