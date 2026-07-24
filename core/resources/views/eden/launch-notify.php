<?php $startup = $startup ?? null; if (!$startup) return; ?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All apps</a>
    <h1>Notify me when <?= e($startup->name) ?> launches</h1>
    <p>This app isn't live yet. Enter your email and we'll send you one message when it goes live on <?= e(function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden') ?>.</p>
  </div>
</section>

<div class="wrap content-block">
  <?php if (session('success')): ?>
  <div class="flash flash-success"><?= e(session('success')) ?></div>
  <?php endif; ?>
  <form action="<?= e(route('launch-notify.store', $startup->slug)) ?>" method="post" class="launch-notify-form" style="max-width: 400px;">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <label for="launch-notify-email" class="form-label">Email</label>
    <input type="email" id="launch-notify-email" name="email" class="form-input" placeholder="you@example.com" value="<?= e(old('email')) ?>" required>
    <button type="submit" class="btn btn-primary" style="margin-top: 12px;"><i class="fa-solid fa-bell" aria-hidden="true"></i> Notify me</button>
  </form>
</div>
