<?php
/** @var \App\Models\AdSpot|null $ad */
/** @var string $buyUrl */
/** @var string $emptyTitle */
/** @var string $emptyCopy */
/** @var int $maxWidth */
$ad = $ad ?? null;
$buyUrl = $buyUrl ?? url('/advertise');
$emptyTitle = $emptyTitle ?? 'Ad spot available';
$emptyCopy = $emptyCopy ?? '';
$maxWidth = $maxWidth ?? 728;
?>
<?php if ($ad): ?>
<?php
  $path = $ad->image_path ?? '';
  $isExternal = is_string($path) && $path !== '' && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'));
  $src = $isExternal ? $path : asset($path);
?>
<div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border, #e2e8f0); background: #0f172a;">
  <a href="<?= e($ad->target_url) ?>" target="_blank" rel="noopener noreferrer sponsored" style="display: block;">
    <img src="<?= e($src) ?>" alt="Sponsored ad" style="display: block; width: 100%; max-width: <?= (int) $maxWidth ?>px; height: auto; margin: 0 auto;">
  </a>
</div>
<?php else: ?>
<div style="border-radius: 8px; border: 1px dashed var(--border, #e2e8f0); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; background: rgba(15,23,42,0.6);">
  <div>
    <p style="margin: 0 0 4px; font-weight: 600;"><?= e($emptyTitle) ?></p>
    <?php if ($emptyCopy !== ''): ?>
    <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted, #94a3b8);"><?= e($emptyCopy) ?></p>
    <?php endif; ?>
  </div>
  <div>
    <a href="<?= e($buyUrl) ?>" class="btn btn-primary">Buy this ad spot</a>
  </div>
</div>
<?php endif; ?>
