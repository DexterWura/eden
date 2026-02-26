<?php
$s = $startup ?? null;
if (!$s) return;
$logoPath = $s->logo_path ?? null;
$logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
$foundersDisplay = $s->founders_display ?? [];
$productImages = $s->product_images ?? [];
?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All startups</a>
    <div class="startup-hero">
      <div class="startup-hero-logo">
        <?php if ($logoPath): ?><img src="<?= e(asset($logoPath)) ?>" alt="" class="startup-hero-logo-img"><?php else: ?><?= e($logoLetters) ?><?php endif; ?>
      </div>
      <div>
        <h1><?= e($s->name) ?></h1>
        <?php if ($s->tagline): ?><p class="tagline"><?= e($s->tagline) ?></p><?php endif; ?>
        <div class="startup-meta">
          <?php if ($s->category): ?><span><?= e($s->category) ?></span><?php endif; ?>
          <?php if ($s->location): ?><span><?= e($s->location) ?></span><?php endif; ?>
          <?php if ($s->launch_date): ?><span><?= $s->launch_date->format('F Y') ?></span><?php endif; ?>
        </div>
        <div class="upvote-ui" style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
          <?php $hasUpvoted = $hasUpvoted ?? false; ?>
          <?php if ($hasUpvoted): ?>
          <span class="upvote-btn" style="opacity: 0.8; cursor: default;" aria-label="Upvoted"><i class="fa-solid fa-arrow-up"></i></span>
          <span class="upvote-count"><?= (int)$s->upvotes ?></span>
          <span style="font-size: 0.875rem; color: var(--text-muted, #64748b);">You upvoted</span>
          <?php else: ?>
          <form action="<?= e(route('startup.upvote', $s->slug)) ?>" method="POST" style="display: inline;">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
          </form>
          <span class="upvote-count"><?= (int)$s->upvotes ?></span>
          <?php if (!auth()->check()): ?>
          <span style="font-size: 0.875rem; color: var(--text-muted, #64748b);">Log in to upvote</span>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="wrap">
  <?php if (!empty($productImages)): ?>
  <section class="startup-section startup-product-images">
    <h2>Product</h2>
    <div class="product-images-grid">
      <?php foreach ($productImages as $img): ?>
      <div class="product-image-wrap"><img src="<?= e(asset($img)) ?>" alt="Product"></div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($s->description): ?>
  <section class="startup-section">
    <h2>About</h2>
    <p><?= nl2br(e($s->description)) ?></p>
  </section>
  <?php endif; ?>

  <?php if (count($foundersDisplay) > 0 || $s->founder_email || $s->founder_twitter_url || $s->founder_linkedin_url): ?>
  <section class="startup-section">
    <h2>Founder<?= count($foundersDisplay) > 1 ? 's' : '' ?></h2>
    <?php if (count($foundersDisplay) > 0): ?>
    <div class="startup-founders">
      <?php foreach ($foundersDisplay as $f): ?>
      <div class="startup-founder-block">
        <span class="startup-founder-avatar" title="<?= e($f['name']) ?>">
          <?php if (!empty($f['photo_url'])): ?><img src="<?= e(asset($f['photo_url'])) ?>" alt=""><?php else: ?><span class="startup-founder-initials"><?= e(\App\Models\Startup::founderInitials($f['name'])) ?></span><?php endif; ?>
        </span>
        <strong><?= e($f['name']) ?></strong>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($s->founder_email): ?><p><a href="mailto:<?= e($s->founder_email) ?>"><?= e($s->founder_email) ?></a></p><?php endif; ?>
    <div class="card-links" style="margin-top: 8px;">
      <?php if (!empty($s->founder_twitter_url)): ?><a href="<?= e($s->founder_twitter_url) ?>" target="_blank" rel="noopener" aria-label="Founder on X"><i class="fa-brands fa-x-twitter"></i> X</a><?php endif; ?>
      <?php if (!empty($s->founder_linkedin_url)): ?><a href="<?= e($s->founder_linkedin_url) ?>" target="_blank" rel="noopener" aria-label="Founder on LinkedIn"><i class="fa-brands fa-linkedin-in"></i> LinkedIn</a><?php endif; ?>
    </div>
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
