<?php
$startups = $startups ?? collect();
$productOfDayId = $productOfDayId ?? null;
?>
<section class="page-head">
  <div class="wrap">
    <h1>Apps for sale</h1>
    <p>Browse apps listed for sale on <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer">FLIPit</a>. Buy an existing business and hit the ground running.</p>
  </div>
</section>

<div class="wrap content-block">
  <?php $forSaleAd = $forSaleAd ?? null; ?>
  <div class="for-sale-ad-spot" style="margin: 0 0 24px;">
    <?php
      $ad = $forSaleAd;
      $buyUrl = url('/advertise/for-sale');
      $emptyTitle = 'Ad spot for buyers';
      $emptyCopy = 'Reach founders and operators looking to buy apps listed for sale.';
      $maxWidth = 728;
      include __DIR__ . '/partials/ad-spot.php';
    ?>
  </div>
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
  <p class="section-empty">No apps listed for sale at the moment. Check back later or <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer">list yours on FLIPit</a>.</p>
  <?php endif; ?>
</div>
