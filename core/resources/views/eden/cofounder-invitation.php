<?php
$pageTitle = 'Co-founder invitation';
$metaRobots = 'noindex,nofollow';
$content = function () use ($invitation, $token) { ?>
<main class="wrap" style="max-width:720px;padding:80px 20px;">
  <h1>Join <?= e($invitation->startup->name) ?></h1>
  <p>You were invited to help manage this startup on Eden. Invitations are tied to the invited email address and can only be used once.</p>
  <?php if (auth()->check()): ?>
    <?php if (mb_strtolower(auth()->user()->email) === mb_strtolower($invitation->email)): ?>
    <form method="post" action="<?= e(route('cofounder-invitations.accept', ['token' => $token])) ?>">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
      <button type="submit" class="btn btn-primary">Accept invitation</button>
    </form>
    <?php else: ?>
    <p>Sign in as <strong><?= e($invitation->email) ?></strong> to accept this invitation.</p>
    <?php endif; ?>
  <?php else: ?>
    <a class="btn btn-primary" href="<?= e(route('login', ['redirect' => request()->fullUrl()])) ?>">Log in to accept</a>
  <?php endif; ?>
</main>
<?php };
ob_start();
$content();
$pageContent = ob_get_clean();
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title><?= e($pageTitle) ?></title><link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>"></head>
<body><?= $pageContent ?></body>
</html>
