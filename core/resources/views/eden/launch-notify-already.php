<?php $startup = $startup ?? null; if (!$startup) return; ?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All startups</a>
    <h1><?= e($startup->name) ?> is live</h1>
    <p>This startup is already live on the directory.</p>
    <a href="<?= e(url('/startup/' . $startup->slug)) ?>" class="btn btn-primary" style="margin-top: 16px;">View startup</a>
  </div>
</section>
