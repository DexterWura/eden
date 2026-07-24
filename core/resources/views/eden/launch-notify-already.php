<?php $startup = $startup ?? null; if (!$startup) return; ?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All apps</a>
    <h1><?= e($startup->name) ?> is live</h1>
    <p>This app is already live on the directory.</p>
    <a href="<?= e(url('/startup/' . $startup->slug)) ?>" class="btn btn-primary" style="margin-top: 16px;">View app</a>
  </div>
</section>
