<?php
$s = $startup ?? null;
if (!$s) return;
$url = url('/startup/' . e($s->slug));
$logoPath = $s->logo_path ?? null;
$logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
$foundersDisplay = $s->founders_display ?? [];
$searchText = implode(' ', array_filter([$s->name, $s->tagline, $s->category, $s->location, $s->founder_name, implode(' ', array_column($foundersDisplay, 'name'))], fn($v) => $v !== null && $v !== ''));
$badgeLabel = $badgeLabel ?? null;
?>
<a href="<?= $url ?>" class="deal-card" data-search="<?= e(mb_strtolower($searchText)) ?>">
  <?php if ($badgeLabel): ?><span class="deal-card-badge"><?= e($badgeLabel) ?></span><?php endif; ?>
  <div class="deal-card-top">
    <div class="deal-card-logo">
      <?php if ($logoPath): ?><img src="<?= e(asset($logoPath)) ?>" alt=""><?php else: ?><?= e($logoLetters) ?><?php endif; ?>
    </div>
    <div class="deal-card-head">
      <h3 class="deal-card-name"><?= e($s->name) ?></h3>
      <p class="deal-card-category"><?= e($s->category ?: $s->short_description ?: '—') ?></p>
    </div>
  </div>
  <div class="deal-card-metrics">
    <div class="deal-metric">
      <span class="deal-metric-label">Upvotes</span>
      <span class="deal-metric-value"><?= (int)$s->upvotes ?></span>
    </div>
    <div class="deal-metric">
      <span class="deal-metric-label">Category</span>
      <span class="deal-metric-value"><?= e($s->category ?: '—') ?></span>
    </div>
    <div class="deal-metric">
      <span class="deal-metric-label">Launched</span>
      <span class="deal-metric-value"><?= $s->launch_date ? $s->launch_date->format('Y') : '—' ?></span>
    </div>
  </div>
</a>
