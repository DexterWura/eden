<?php
$errors = [];
$ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appName = trim($_POST['app_name'] ?? 'Eden');
    $appUrl = rtrim(trim($_POST['app_url'] ?? ''), '/');
    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_password'] ?? '';
    if (empty($appName)) $errors[] = 'App name required';
    if (empty($appUrl) || !filter_var($appUrl, FILTER_VALIDATE_URL)) $errors[] = 'Valid app URL required';
    if (empty($adminName)) $errors[] = 'Admin name required';
    if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email required';
    if (strlen($adminPass) < 8) $errors[] = 'Admin password must be at least 8 characters';
    if (empty($errors)) {
        $_SESSION['app_config'] = ['name' => $appName, 'url' => $appUrl];
        $_SESSION['admin_user'] = ['name' => $adminName, 'email' => $adminEmail, 'password' => $adminPass];
        $ok = true;
    }
}
$app = $_SESSION['app_config'] ?? ['name' => 'Eden', 'url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')];
$admin = $_SESSION['admin_user'] ?? ['name' => '', 'email' => '', 'password' => ''];
?>
<h2>Step 3: Config &amp; admin</h2>
<?php if ($ok): ?>
    <div class="alert alert-success">Saved.</div>
    <p style="margin-top: 24px;"><a href="?step=4<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>" class="btn btn-primary">Next: Install →</a></p>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="POST" action="?step=3<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>">
        <div class="form-group">
            <label>Application name</label>
            <input type="text" name="app_name" value="<?= htmlspecialchars($app['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Application URL</label>
            <input type="url" name="app_url" value="<?= htmlspecialchars($app['url']) ?>" required>
        </div>
        <hr style="border-color:#2a2e3d; margin: 24px 0;">
        <h3 style="font-size:1.1rem; margin-bottom:12px;">Admin user</h3>
        <div class="form-group">
            <label>Admin name</label>
            <input type="text" name="admin_name" value="<?= htmlspecialchars($admin['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Admin email</label>
            <input type="email" name="admin_email" value="<?= htmlspecialchars($admin['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Admin password (min 8 characters)</label>
            <input type="password" name="admin_password" value="<?= htmlspecialchars($admin['password']) ?>" required minlength="8">
        </div>
        <p style="margin-top: 24px;">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="?step=2<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>" class="btn btn-ghost">Back</a>
        </p>
    </form>
<?php endif; ?>
