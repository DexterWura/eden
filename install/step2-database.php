<?php
$errors = [];
$ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? '127.0.0.1');
    $port = trim($_POST['db_port'] ?? '3306');
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = $_POST['db_pass'] ?? '';
    if (empty($name)) $errors[] = 'Database name required';
    if (empty($user)) $errors[] = 'Database user required';
    if (empty($errors)) {
        try {
            $dsn = "mysql:host=" . addslashes($host) . ";port=" . (int)$port . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]);
            $safe = str_replace('`', '``', $name);
            try { $pdo->exec("USE `{$safe}`"); } catch (PDOException $e) {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$safe}`");
            }
            $pdo->query("SELECT 1");
            $_SESSION['db_config'] = ['host' => $host, 'port' => $port, 'database' => $name, 'username' => $user, 'password' => $pass];
            $ok = true;
        } catch (PDOException $e) {
            $errors[] = 'Connection failed: ' . htmlspecialchars($e->getMessage());
        }
    }
}
$cfg = $_SESSION['db_config'] ?? ['host' => '127.0.0.1', 'port' => '3306', 'database' => '', 'username' => '', 'password' => ''];
?>
<h2>Step 2: Database</h2>
<?php if ($ok): ?>
    <div class="alert alert-success">Database connection OK. Saved.</div>
    <p style="margin-top: 24px;"><a href="?step=3<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>" class="btn btn-primary">Next: Config &amp; admin →</a></p>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="POST" action="?step=2<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>">
        <div class="form-group">
            <label>Database host</label>
            <input type="text" name="db_host" value="<?= htmlspecialchars($cfg['host']) ?>" required>
        </div>
        <div class="form-group">
            <label>Port</label>
            <input type="text" name="db_port" value="<?= htmlspecialchars($cfg['port']) ?>" required>
        </div>
        <div class="form-group">
            <label>Database name</label>
            <input type="text" name="db_name" value="<?= htmlspecialchars($cfg['database']) ?>" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="db_user" value="<?= htmlspecialchars($cfg['username']) ?>" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="db_pass" value="<?= htmlspecialchars($cfg['password']) ?>">
        </div>
        <p style="margin-top: 24px;">
            <button type="submit" class="btn btn-primary">Test &amp; save</button>
            <a href="?step=1<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>" class="btn btn-ghost">Back</a>
        </p>
    </form>
<?php endif; ?>
