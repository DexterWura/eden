<?php
$installed = false;
try {
    if (!defined('LARAVEL_START')) define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../core/vendor/autoload.php';
    $app = require __DIR__ . '/../core/bootstrap/app.php';
    if (is_object($app) && method_exists($app, 'make')) {
        $installed = $app->make('cache')->get('EdenInstalled');
    }
    if (!$installed && file_exists(__DIR__ . '/../.env')) $installed = true;
} catch (Exception $e) { }
session_destroy();
?>
<h2>Step 5: Complete</h2>
<?php if ($installed): ?>
    <div class="alert alert-success">
        <strong>Eden is installed.</strong> You can log in with the admin account you created.
    </div>
    <p><a href="/" class="btn btn-primary">Go to site</a> <a href="/admin-dashboard.html" class="btn btn-ghost">Admin dashboard</a></p>
    <p style="margin-top:20px; color:#8b90a0; font-size:0.9rem;">Consider deleting or protecting the <code>/install</code> folder in production.</p>
<?php else: ?>
    <div class="alert alert-danger">Installation could not be verified.</div>
    <p><a href="?step=4&force=1" class="btn btn-primary">Retry</a> <a href="?step=1&force=1" class="btn btn-ghost">Start over</a></p>
<?php endif; ?>
