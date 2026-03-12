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
  <?php $raisingAd = $raisingAd ?? null; ?>
  <div class="raising-ad-spot" style="margin: 0 0 24px;">
    <?php if ($raisingAd): ?>
    <?php
      $raisingAdPath = $raisingAd->image_path ?? '';
      $raisingAdIsExternal = is_string($raisingAdPath) && ($raisingAdPath !== '') && (str_starts_with($raisingAdPath, 'http://') || str_starts_with($raisingAdPath, 'https://'));
      $raisingAdSrc = $raisingAdIsExternal ? $raisingAdPath : asset($raisingAdPath);
    ?>
    <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border, #e2e8f0); background: #0f172a;">
      <a href="<?= e($raisingAd->target_url) ?>" target="_blank" rel="noopener noreferrer" style="display: block;">
        <img src="<?= e($raisingAdSrc) ?>" alt="Sponsored ad" style="display: block; width: 100%; max-width: 728px; height: auto; margin: 0 auto;">
      </a>
    </div>
    <?php else: ?>
    <div style="border-radius: 8px; border: 1px dashed var(--border, #e2e8f0); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; background: rgba(15,23,42,0.6);">
      <div>
        <p style="margin: 0 0 4px; font-weight: 600;">Ad spot for investors</p>
        <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted, #94a3b8);">
          Promote your fund, deal newsletter, or syndicate to founders raising capital.
        </p>
      </div>
      <div>
        <a href="<?= e(url('/advertise/blog')) ?>" class="btn btn-primary">Buy this ad spot</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
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
