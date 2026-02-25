<?php

/**
 * Fallback when the web server document root is the project root.
 * With Apache, the root .htaccess rewrites requests to public/ so this file is not used.
 * If you see this page: enable the root .htaccess (AllowOverride) or set the document root to the public/ directory.
 */
$publicDir = __DIR__ . '/public';
if (is_dir($publicDir) && file_exists($publicDir . '/index.php')) {
    require $publicDir . '/index.php';
    return;
}
header('Content-Type: text/plain; charset=utf-8', true, 500);
echo 'Misconfiguration: use the root .htaccess (Apache) or set document root to the public/ directory.';
