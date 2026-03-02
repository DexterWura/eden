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
    <?php if (($s->status ?? '') === 'pending'): ?>
    <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:14px 18px;margin-bottom:16px;font-size:0.92rem;color:#92400e;text-align:center">
      <i class="fa-solid fa-clock" style="margin-right:6px"></i>
      This startup is pending review and is not yet visible to the public.
    </div>
    <?php endif; ?>
    <a href="<?= e(url('/')) ?>" class="back-link">&larr; All startups</a>
    <div class="startup-hero">
      <div class="startup-hero-logo" role="img" aria-label="<?= e($s->name) ?> logo">
        <?php if ($logoPath): ?><img src="<?= e(asset($logoPath)) ?>" alt="<?= e($s->name) ?> – logo" class="startup-hero-logo-img" width="80" height="80" loading="eager"><?php else: ?><?= e($logoLetters) ?><?php endif; ?>
      </div>
      <div>
        <h1><?= e($s->name) ?></h1>
        <?php $isProductOfDay = $isProductOfDay ?? false; ?>
        <?php if ($isProductOfDay): ?><span class="badge badge-product-of-day" style="display: inline-block; margin-bottom: 8px;">Product of the day</span><?php endif; ?>
        <?php if ($s->tagline): ?><p class="tagline"><?= e($s->tagline) ?></p><?php endif; ?>
        <div class="startup-meta">
          <?php if ($s->category): ?><span><?= e($s->category) ?></span><?php endif; ?>
          <?php if ($s->location): ?><span><?= e($s->location) ?></span><?php endif; ?>
          <?php if ($s->launch_date): ?><span><?= $s->launch_date->format('F Y') ?></span><?php endif; ?>
        </div>
        <div class="upvote-ui" style="margin-top: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
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
          <?php if (!empty($s->website)): ?>
          <a href="<?= e(url('/startup/' . $s->slug . '/out')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="margin-left: 4px;"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Visit website</a>
          <?php endif; ?>
          <a href="<?= e(route('startup.claim', $s->slug)) ?>" class="btn btn-ghost" style="margin-left: 4px;"><i class="fa-solid fa-hand-holding-hand" aria-hidden="true"></i> Claim this startup</a>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="wrap">
  <?php if (!empty($productImages)): ?>
  <section class="startup-section startup-product-images" aria-labelledby="product-heading">
    <h2 id="product-heading">Product</h2>
    <div class="product-images-grid">
      <?php foreach ($productImages as $i => $img): ?>
      <div class="product-image-wrap"><img src="<?= e(asset($img)) ?>" alt="<?= e($s->name) ?> – product<?= count($productImages) > 1 ? ' ' . ((int)$i + 1) : '' ?>" width="400" height="300" loading="lazy"></div>
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

  <?php if (count($foundersDisplay) > 0): ?>
  <section class="startup-section">
    <h2>Founder<?= count($foundersDisplay) > 1 ? 's' : '' ?></h2>
    <div class="startup-founders startup-founders--detailed">
      <?php foreach ($foundersDisplay as $f): ?>
      <div class="startup-founder-block startup-founder-block--card">
        <span class="startup-founder-avatar" title="<?= e($f['name']) ?>">
          <?php if (!empty($f['photo_url'])): ?><img src="<?= e(asset($f['photo_url'])) ?>" alt=""><?php else: ?><span class="startup-founder-initials"><?= e(\App\Models\Startup::founderInitials($f['name'])) ?></span><?php endif; ?>
        </span>
        <div class="startup-founder-info">
          <strong class="startup-founder-name"><?= e($f['name']) ?></strong>
          <?php if (!empty($f['email'])): ?><p class="startup-founder-email"><a href="mailto:<?= e($f['email']) ?>"><?= e($f['email']) ?></a></p><?php endif; ?>
          <?php if (!empty($f['twitter_url']) || !empty($f['linkedin_url'])): ?>
          <div class="startup-founder-links" aria-label="Social links for <?= e($f['name']) ?>">
            <?php if (!empty($f['twitter_url'])): ?><a href="<?= e($f['twitter_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($f['name']) ?> on X"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i> X</a><?php endif; ?>
            <?php if (!empty($f['linkedin_url'])): ?><a href="<?= e($f['linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($f['name']) ?> on LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i> LinkedIn</a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
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
    <a href="<?= e(route('startup.claim', $s->slug)) ?>" class="btn btn-primary"><i class="fa-solid fa-hand-holding-hand" aria-hidden="true"></i> Claim this startup</a>
    <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Browse more startups</a>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
  </div>
</div>
