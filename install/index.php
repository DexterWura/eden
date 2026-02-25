<?php
/**
 * Eden – Installation wizard
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 1);
set_time_limit(300);

session_start();

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = ($step < 1 || $step > 5) ? 1 : $step;
$force = isset($_GET['force']) && $_GET['force'] === '1';

if ($step === 4 && (!isset($_SESSION['db_config']) || !isset($_SESSION['app_config']) || !isset($_SESSION['admin_user']))) {
    header('Location: ?step=' . (isset($_SESSION['db_config']) ? (isset($_SESSION['app_config']) ? 3 : 2) : 2) . ($force ? '&force=1' : ''));
    exit;
}

if (!$force) {
    $envPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env';
    if (file_exists($envPath)) {
        $autoloader = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (file_exists($autoloader)) {
            try {
                if (!defined('LARAVEL_START')) define('LARAVEL_START', microtime(true));
                require $autoloader;
                $app = require __DIR__ . '/../core/bootstrap/app.php';
                if (is_object($app) && method_exists($app, 'make')) {
                    $cache = $app->make('cache');
                    if ($cache->get('EdenInstalled')) {
                        header('Location: /');
                        exit;
                    }
                }
            } catch (Exception $e) { }
        }
    }
}

$baseCss = '/css/install.css';
$hasCoreCss = false;
if (file_exists(__DIR__ . '/../core/public/css/install.css')) {
    $hasCoreCss = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install – Eden</title>
    <?php if ($hasCoreCss): ?><link rel="stylesheet" href="<?= htmlspecialchars($baseCss) ?>"><?php endif; ?>
    <style>
        body { background: #0a0b0f; color: #e8eaef; font-family: system-ui, sans-serif; padding: 20px; }
        .install-wrap { max-width: 640px; margin: 0 auto; }
        .install-box { background: #12141c; border: 1px solid #2a2e3d; border-radius: 12px; padding: 28px; }
        h1 { font-size: 1.5rem; margin-bottom: 8px; color: #00d4aa; }
        .step-bar { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
        .step-bar span { padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; background: #1a1d28; color: #8b90a0; }
        .step-bar span.active { background: rgba(0,212,170,0.2); color: #00d4aa; }
        .step-bar span.done { background: rgba(0,212,170,0.1); color: #8b90a0; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 6px; color: #e8eaef; }
        .form-group input, .form-group select { width: 100%; padding: 10px 14px; background: #1a1d28; border: 1px solid #2a2e3d; border-radius: 8px; color: #e8eaef; font-size: 1rem; }
        .form-group input:focus { border-color: #00d4aa; outline: none; }
        .form-group small { display: block; margin-top: 4px; color: #8b90a0; font-size: 0.85rem; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; text-decoration: none; }
        .btn-primary { background: #00d4aa; color: #0a0b0f; }
        .btn-primary:hover { opacity: 0.95; }
        .btn-ghost { background: transparent; color: #8b90a0; }
        .btn-ghost:hover { color: #e8eaef; }
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: rgba(0,212,170,0.15); border: 1px solid rgba(0,212,170,0.3); color: #00d4aa; }
        .alert-danger { background: rgba(255,71,103,0.15); border: 1px solid rgba(255,71,103,0.3); color: #ff4767; }
        .alert-info { background: rgba(0,212,170,0.08); border: 1px solid #2a2e3d; color: #8b90a0; }
        .check { padding: 10px 14px; margin: 6px 0; border-left: 4px solid #2a2e3d; border-radius: 0 8px 8px 0; background: #1a1d28; }
        .check.ok { border-color: #00d4aa; }
        .check.fail { border-color: #ff4767; }
        ul { margin: 0; padding-left: 20px; }
        a { color: #00d4aa; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="install-wrap">
        <div class="install-box">
            <h1>Eden installation</h1>
            <p style="color: #8b90a0; margin-bottom: 24px;">Startup directory – install wizard.</p>
            <div class="step-bar">
                <span class="<?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'done' : '' ?>">1. Requirements</span>
                <span class="<?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'done' : '' ?>">2. Database</span>
                <span class="<?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'done' : '' ?>">3. Config &amp; admin</span>
                <span class="<?= $step >= 4 ? 'active' : '' ?> <?= $step > 4 ? 'done' : '' ?>">4. Install</span>
                <span class="<?= $step >= 5 ? 'active' : '' ?>">5. Done</span>
            </div>
            <?php
            if ($step === 1) include __DIR__ . '/step1-requirements.php';
            elseif ($step === 2) include __DIR__ . '/step2-database.php';
            elseif ($step === 3) include __DIR__ . '/step3-config.php';
            elseif ($step === 4) include __DIR__ . '/step4-install.php';
            else include __DIR__ . '/step5-complete.php';
            ?>
        </div>
    </div>
</body>
</html>
