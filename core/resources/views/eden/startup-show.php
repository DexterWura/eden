<?php
$s = $startup ?? null;
if (!$s) return;
$logo = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All startups</a>
    <div class="startup-hero">
      <div class="startup-hero-logo"><?= e($logo) ?></div>
      <div>
        <h1><?= e($s->name) ?></h1>
        <?php if ($s->tagline): ?><p class="tagline"><?= e($s->tagline) ?></p><?php endif; ?>
        <div class="startup-meta">
          <?php if ($s->category): ?><span><?= e($s->category) ?></span><?php endif; ?>
          <?php if ($s->location): ?><span><?= e($s->location) ?></span><?php endif; ?>
          <?php if ($s->launch_date): ?><span><?= $s->launch_date->format('F Y') ?></span><?php endif; ?>
        </div>
        <div class="upvote-ui" style="margin-top: 12px;">
          <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          <span class="upvote-count"><?= (int)$s->upvotes ?></span>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="wrap">
  <?php if ($s->description): ?>
  <section class="startup-section">
    <h2>About</h2>
    <p><?= nl2br(e($s->description)) ?></p>
  </section>
  <?php endif; ?>

  <?php if ($s->founder_name): ?>
  <section class="startup-section">
    <h2>Founder</h2>
    <p><strong><?= e($s->founder_name) ?></strong></p>
  </section>
  <?php endif; ?>

  <section class="startup-section">
    <h2>Links</h2>
    <div class="card-links">
      <?php if ($s->website): ?><a href="<?= e($s->website) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-globe"></i> Website</a><?php endif; ?>
      <?php if (!empty($s->twitter_url)): ?><a href="<?= e($s->twitter_url) ?>" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
      <?php if (!empty($s->linkedin_url)): ?><a href="<?= e($s->linkedin_url) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
      <?php if (!$s->website && empty($s->twitter_url) && empty($s->linkedin_url)): ?><span class="text-muted">No links yet.</span><?php endif; ?>
    </div>
  </section>

  <div class="cta-strip">
    <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Browse more startups</a>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
  </div>
</div>
