<?php
$startups = $startups ?? collect();
$productOfDayId = $productOfDayId ?? null;
?>
<section class="page-head">
  <div class="wrap">
    <h1>Startups for sale</h1>
    <p>Browse startups listed for sale on <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer">FLIPit</a>. Buy an existing business and hit the ground running.</p>
  </div>
</section>

<div class="wrap content-block">
  <?php $forSaleAd = $forSaleAd ?? null; ?>
  <div class="for-sale-ad-spot" style="margin: 0 0 24px;">
    <?php if ($forSaleAd): ?>
    <?php
      $forSaleAdPath = $forSaleAd->image_path ?? '';
      $forSaleAdIsExternal = is_string($forSaleAdPath) && ($forSaleAdPath !== '') && (str_starts_with($forSaleAdPath, 'http://') || str_starts_with($forSaleAdPath, 'https://'));
      $forSaleAdSrc = $forSaleAdIsExternal ? $forSaleAdPath : asset($forSaleAdPath);
    ?>
    <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border, #e2e8f0); background: #0f172a;">
      <a href="<?= e($forSaleAd->target_url) ?>" target="_blank" rel="noopener noreferrer" style="display: block;">
        <img src="<?= e($forSaleAdSrc) ?>" alt="Sponsored ad" style="display: block; width: 100%; max-width: 728px; height: auto; margin: 0 auto;">
      </a>
    </div>
    <?php else: ?>
    <div style="border-radius: 8px; border: 1px dashed var(--border, #e2e8f0); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; background: rgba(15,23,42,0.6);">
      <div>
        <p style="margin: 0 0 4px; font-weight: 600;">Ad spot for buyers</p>
        <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted, #94a3b8);">
          Reach founders and operators looking to buy startups listed for sale.
        </p>
      </div>
      <div>
        <a href="<?= e(url('/advertise/blog')) ?>" class="btn btn-primary">Buy this ad spot</a>
      </div>
    </div>
    <?php endif; ?>
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
  <p class="section-empty">No startups listed for sale at the moment. Check back later or <a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer">list yours on FLIPit</a>.</p>
  <?php endif; ?>
</div>
