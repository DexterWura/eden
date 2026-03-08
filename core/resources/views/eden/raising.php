<?php
$startups = $startups ?? collect();
$categories = $categories ?? collect();
$categoryFilter = $categoryFilter ?? null;
$productOfDayId = $productOfDayId ?? null;
?>
<section class="page-head">
  <div class="wrap">
    <h1>Startups raising funding</h1>
    <p>Discover startups currently raising or looking for investors. Filter by category.</p>
  </div>
</section>

<div class="wrap content-block">
  <?php if ($categories->isNotEmpty()): ?>
  <div class="filters filters--categories" style="margin-bottom: 24px;">
    <a href="<?= e(url('/raising')) ?>" class="pill<?= $categoryFilter === null || $categoryFilter === '' ? ' active' : '' ?>">All</a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= e(url('/raising?' . http_build_query(['category' => $cat->category]))) ?>" class="pill<?= $categoryFilter === $cat->category ? ' active' : '' ?>"><?= e($cat->category) ?> (<?= (int)$cat->count ?>)</a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($startups->isNotEmpty()): ?>
  <div class="startup-list">
    <?php foreach ($startups as $startup):
      $rank = null;
      $showRank = false;
      $cardVariant = null;
      include __DIR__ . '/_startup-card.php';
    endforeach; ?>
  </div>
  <?php else: ?>
  <p class="section-empty">No startups raising right now. <a href="<?= e(url('/submit')) ?>">Submit your startup</a> and add a funding round in your dashboard.</p>
  <?php endif; ?>
</div>
