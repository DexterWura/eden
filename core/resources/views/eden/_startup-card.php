<?php
$s = $startup ?? null;
if (!$s) return;
$rank = $rank ?? null;
$showRank = isset($showRank) ? $showRank : false;
$url = url('/startup/' . e($s->slug));
$logo = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
$searchText = implode(' ', array_filter([$s->name, $s->tagline, $s->category, $s->location, $s->founder_name], fn($v) => $v !== null && $v !== ''));
?>
<div class="startup-card<?= $s->is_featured ? ' featured' : '' ?>" data-search="<?= e(mb_strtolower($searchText)) ?>">
  <?php if ($showRank && $rank !== null): ?><span class="card-rank"><?= (int)$rank ?></span><?php endif; ?>
  <div class="card-top">
    <div class="card-logo"><?= e($logo) ?></div>
    <div class="card-badges">
      <?php if ($s->is_featured): ?><span class="badge">Featured</span><?php endif; ?>
      <?php if ($s->launch_date && $s->launch_date->isToday()): ?><span class="badge launch">Launch</span><?php endif; ?>
    </div>
    <div class="upvote-ui">
      <button type="button" class="upvote-btn" aria-label="Upvote"><i class="fa-solid fa-arrow-up"></i></button>
      <span class="upvote-count"><?= (int)$s->upvotes ?></span>
    </div>
  </div>
  <a href="<?= $url ?>" class="card-link">
    <h3 class="card-title"><?= e($s->name) ?></h3>
    <p class="card-desc"><?= e($s->short_description) ?></p>
    <div class="card-meta">
      <?php if ($s->category): ?><span><?= e($s->category) ?></span><?php endif; ?>
      <?php if ($s->location): ?><span><?= e($s->location) ?></span><?php endif; ?>
      <span><?= $s->launch_date ? $s->launch_date->format('Y') : '—' ?></span>
    </div>
    <?php if ($s->founder_name): ?><p class="card-founder">Founded by <strong><?= e($s->founder_name) ?></strong></p><?php endif; ?>
    <div class="card-links">
      <?php if ($s->website): ?><a href="<?= e($s->website) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-globe" aria-hidden="true"></i> Website</a><?php endif; ?>
      <?php if (!empty($s->twitter_url)): ?><a href="<?= e($s->twitter_url) ?>" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
      <?php if (!empty($s->linkedin_url)): ?><a href="<?= e($s->linkedin_url) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
    </div>
  </a>
</div>
