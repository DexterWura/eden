<?php
$startups = $startups ?? collect();
$productOfDayId = $productOfDayId ?? null;
$savedStartupIds = $startups->pluck('id')->toArray();
?>
<section class="page-head">
  <div class="wrap">
    <h1>My saved apps</h1>
    <p>Apps you've saved for later. Remove any time.</p>
  </div>
</section>

<div class="wrap content-block">
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
  <p class="section-empty">You haven't saved any apps yet. Browse the <a href="<?= e(url('/')) ?>">directory</a> and click the bookmark to save.</p>
  <?php endif; ?>
</div>
