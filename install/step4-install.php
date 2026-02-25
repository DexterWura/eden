<?php
if (!isset($_SESSION['db_config']) || !isset($_SESSION['app_config']) || !isset($_SESSION['admin_user'])) {
    header('Location: ?step=2');
    exit;
}
$db = $_SESSION['db_config'];
$app = $_SESSION['app_config'];
$admin = $_SESSION['admin_user'];

function escapeEnv($v) {
    if (preg_match('/[\s"\'#\\\]/', $v)) {
        return '"' . str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $v) . '"';
    }
    return $v;
}

$errors = [];
$steps = [];
$done = ($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['run_install']);

if ($done) {
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $env = "APP_NAME=" . escapeEnv($app['name']) . "\n";
    $env .= "APP_ENV=production\nAPP_KEY={$appKey}\nAPP_DEBUG=false\n";
    $env .= "APP_URL=" . escapeEnv($app['url']) . "\n\n";
    $env .= "DB_CONNECTION=mysql\nDB_HOST=" . escapeEnv($db['host']) . "\nDB_PORT={$db['port']}\n";
    $env .= "DB_DATABASE=" . escapeEnv($db['database']) . "\nDB_USERNAME=" . escapeEnv($db['username']) . "\nDB_PASSWORD=" . escapeEnv($db['password']) . "\n\n";
    $env .= "CACHE_STORE=file\nSESSION_DRIVER=file\n";

    $envPath = __DIR__ . '/../.env';
    $coreEnvPath = __DIR__ . '/../core/.env';
    if (file_put_contents($envPath, $env) === false) {
        $errors[] = 'Could not write .env';
    } else {
        $steps[] = [true, '.env created'];
        @file_put_contents($coreEnvPath, $env);
    }

    if (empty($errors)) {
        try {
            if (!defined('LARAVEL_START')) define('LARAVEL_START', microtime(true));
            require __DIR__ . '/../core/vendor/autoload.php';
            $laravel = require __DIR__ . '/../core/bootstrap/app.php';
            $laravel->make(\Illuminate\Contracts\Http\Kernel::class)->bootstrap();
            $steps[] = [true, 'Laravel loaded'];

            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $steps[] = [true, 'Migrations run'];

            $hashed = \Illuminate\Support\Facades\Hash::make($admin['password']);
            $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => $hashed,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $steps[] = [true, 'Admin user created'];

            try {
                \Illuminate\Support\Facades\Schema::hasColumn('users', 'is_admin') &&
                \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->update(['is_admin' => true]);
            } catch (Exception $e) { }

            $cache = $laravel->make('cache');
            $cache->put('EdenInstalled', true, now()->addYears(10));
            $steps[] = [true, 'Install flag set'];

            $success = true;
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
            $steps[] = [false, $e->getMessage()];
            $success = false;
        }
    } else {
        $success = false;
    }
}
?>
<h2>Step 4: Install</h2>
<?php if (!$done): ?>
    <div class="alert alert-info">Click below to write .env, run migrations, and create the admin user.</div>
    <form method="POST" action="?step=4<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>">
        <p>
            <button type="submit" name="run_install" value="1" class="btn btn-primary">Run installation</button>
            <a href="?step=3<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>" class="btn btn-ghost">Back</a>
        </p>
    </form>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php foreach ($steps as $s): ?>
        <div class="check <?= $s[0] ? 'ok' : 'fail' ?>"><?= $s[0] ? '✓' : '✗' ?> <?= htmlspecialchars($s[1]) ?></div>
    <?php endforeach; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-top:20px;">Installation complete.</div>
        <p style="margin-top: 24px;"><a href="?step=5" class="btn btn-primary">Finish →</a></p>
    <?php else: ?>
        <p style="margin-top: 24px;">
            <a href="?step=4<?= isset($_GET['force']) && $_GET['force'] === '1' ? '&force=1' : '' ?>" class="btn btn-primary">Retry</a>
            <a href="?step=3" class="btn btn-ghost">Back</a>
        </p>
    <?php endif; ?>
<?php endif; ?>
