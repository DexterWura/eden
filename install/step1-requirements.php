<?php
$checks = [];
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'curl', 'fileinfo'];
foreach ($required as $ext) {
    $checks['ext_' . $ext] = extension_loaded($ext);
}
$checks['php'] = version_compare(PHP_VERSION, '8.1', '>=');

$writableDirs = ['core/storage', 'core/bootstrap/cache', 'core/storage/framework', 'core/storage/logs'];
foreach ($writableDirs as $dir) {
    $full = __DIR__ . '/../' . $dir;
    $checks['w_' . str_replace('/', '_', $dir)] = is_dir($full) ? is_writable($full) : (is_dir(dirname($full)) && is_writable(dirname($full)));
}
$checks['vendor'] = file_exists(__DIR__ . '/../core/vendor/autoload.php');
$allOk = !in_array(false, $checks);
?>
<h2>Step 1: Requirements</h2>
<?php if ($allOk): ?>
    <div class="alert alert-success">All requirements met.</div>
<?php else: ?>
    <div class="alert alert-danger">Fix the issues below before continuing.</div>
<?php endif; ?>
<div class="check <?= $checks['php'] ? 'ok' : 'fail' ?>">PHP <?= PHP_VERSION ?> (need 8.1+)</div>
<?php foreach ($required as $ext): ?>
    <div class="check <?= $checks['ext_' . $ext] ? 'ok' : 'fail' ?>"><?= $ext ?></div>
<?php endforeach; ?>
<?php foreach ($writableDirs as $dir): ?>
    <div class="check <?= $checks['w_' . str_replace('/', '_', $dir)] ? 'ok' : 'fail' ?>"><?= $dir ?> writable</div>
<?php endforeach; ?>
<div class="check <?= $checks['vendor'] ? 'ok' : 'fail' ?>">core/vendor</div>
<p style="margin-top: 24px;">
    <?php if ($allOk): ?>
        <a href="?step=2<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>" class="btn btn-primary">Next: Database →</a>
    <?php else: ?>
        <a href="?step=1" class="btn btn-ghost">Refresh</a>
    <?php endif; ?>
</p>
