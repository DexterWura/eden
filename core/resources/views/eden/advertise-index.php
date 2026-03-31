<?php
/** @var array $spots segment => meta */
?>
<section class="page-head">
  <div class="wrap">
    <h1>Advertise</h1>
    <p>Self-serve banner placements. One payment runs your creative for 30 days.</p>
  </div>
</section>

<div class="wrap content-block">
  <div class="advertise-spots-grid" style="display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
    <?php foreach ($spots as $segment => $meta): ?>
    <div class="card" style="display: flex; flex-direction: column; height: 100%;">
      <h2 style="margin-top: 0; font-size: 1.1rem;"><?= e($meta['label']) ?></h2>
      <p style="color: var(--text-muted); font-size: 0.9rem; flex: 1; margin: 0 0 12px;">
        <?= e($meta['description']) ?>
      </p>
      <p style="margin: 0 0 12px; font-weight: 600;">
        <?= e($meta['width']) ?>×<?= e($meta['height']) ?> · <?= e($meta['currency']) ?><?= number_format($meta['price'], 2) ?>/mo
      </p>
      <a href="<?= e(url('/advertise/' . $segment)) ?>" class="btn btn-primary" style="align-self: flex-start;">Buy this spot</a>
    </div>
    <?php endforeach; ?>
  </div>
  <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border, #e2e8f0); max-width: 720px;">
    <?php $style = 'compact'; include __DIR__ . '/partials/sister-sites.php'; ?>
  </div>
</div>
