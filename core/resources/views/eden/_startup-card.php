<?php
$s = $startup ?? null;
if (!$s) return;
$rank = $rank ?? null;
$showRank = isset($showRank) ? $showRank : false;
$cardVariant = $cardVariant ?? null;
$url = url('/startup/' . e($s->slug));
$logoPath = $s->logo_path ?? null;
$logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
$foundersDisplay = $s->founders_display ?? [];
$searchText = implode(' ', array_filter([$s->name, $s->tagline, $s->category, $s->location, $s->founder_name, implode(' ', array_column($foundersDisplay, 'name'))], fn($v) => $v !== null && $v !== ''));
$isRow = $cardVariant === 'row';
?>
<div class="startup-card<?= $s->is_featured ? ' featured' : '' ?><?= $isRow ? ' startup-card--row' : '' ?>" data-search="<?= e(mb_strtolower($searchText)) ?>">
  <?php if ($showRank && $rank !== null): ?><span class="card-rank"><?= (int)$rank ?></span><?php endif; ?>
  <div class="card-top">
    <div class="card-logo">
      <?php if ($logoPath): ?><img src="<?= e(asset($logoPath)) ?>" alt="" class="card-logo-img"><?php else: ?><?= e($logoLetters) ?><?php endif; ?>
    </div>
    <div class="card-badges">
      <?php $productOfDayId = $productOfDayId ?? null; ?>
      <?php if ($productOfDayId && (int)$s->id === (int)$productOfDayId): ?><span class="badge badge-product-of-day">Product of the day</span><?php endif; ?>
      <?php if ($s->is_featured): ?><span class="badge">Featured</span><?php endif; ?>
      <?php if ($s->launch_date && $s->launch_date->isToday()): ?><span class="badge launch">Launch</span><?php endif; ?>
      <?php if ($s->activeFundingRound): ?><span class="badge badge-funding"><i class="fa-solid fa-hand-holding-dollar"></i> Raising</span><?php endif; ?>
      <?php if ($s->for_sale && !$s->sold_at && $s->flipit_listing_id): ?>
        <?php $flipitUrl = $s->getFlipitListingUrl(); ?>
        <?php if ($flipitUrl): ?><a href="<?= e($flipitUrl) ?>" target="_blank" rel="noopener noreferrer" class="badge badge-for-sale"><i class="fa-solid fa-tag" aria-hidden="true"></i> For sale</a><?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="upvote-ui">
      <button
        type="button"
        class="upvote-btn"
        aria-label="Upvote"
        data-upvote-url="<?= e(route('startup.upvote', $s->slug)) ?>"
      ><i class="fa-solid fa-arrow-up"></i></button>
      <span class="upvote-count"><?= (int)$s->upvotes ?></span>
    </div>
    <?php
    $savedStartupIds = $savedStartupIds ?? [];
    if (auth()->check()):
      $isSaved = in_array((int)$s->id, $savedStartupIds, true);
    ?>
    <div class="save-ui" style="margin-left: 6px;">
      <?php if ($isSaved): ?>
      <form action="<?= e(route('startup.unsave', $s->slug)) ?>" method="post" style="display:inline;" onsubmit="event.stopPropagation();">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="save-btn save-btn--saved" aria-label="Remove from saved"><i class="fa-solid fa-bookmark" aria-hidden="true"></i></button>
      </form>
      <?php else: ?>
      <form action="<?= e(route('startup.save', $s->slug)) ?>" method="post" style="display:inline;" onsubmit="event.stopPropagation();">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="save-btn" aria-label="Save startup"><i class="fa-regular fa-bookmark" aria-hidden="true"></i></button>
      </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <a href="<?= $url ?>" class="card-link">
    <h3 class="card-title"><?= e($s->name) ?></h3>
    <p class="card-desc"><?= e($s->short_description) ?></p>
    <div class="card-meta">
      <?php if ($s->category): ?><span><?= e($s->category) ?></span><?php endif; ?>
      <?php if ($s->location): ?><span><?= e($s->location) ?></span><?php endif; ?>
      <span><?= $s->launch_date ? $s->launch_date->format('Y') : '—' ?></span>
    </div>
    <?php if (count($foundersDisplay) > 0): ?>
    <p class="card-founder">
      <span class="card-founder-avatars">
        <?php foreach ($foundersDisplay as $f): ?>
        <span class="card-founder-avatar" title="<?= e($f['name']) ?>">
          <?php if (!empty($f['photo_url'])): ?><img src="<?= e(asset($f['photo_url'])) ?>" alt="" aria-hidden="true"><?php else: ?><span class="card-founder-initials"><?= e(\App\Models\Startup::founderInitials($f['name'])) ?></span><?php endif; ?>
        </span>
        <?php endforeach; ?>
      </span>
      Founded by <strong><?= e(implode(', ', array_column($foundersDisplay, 'name'))) ?></strong>
    </p>
    <?php endif; ?>
    <div class="card-links">
      <?php if ($s->website): ?><a href="<?= e($s->website) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-globe" aria-hidden="true"></i> Website</a><?php endif; ?>
      <?php if (!empty($s->twitter_url)): ?><a href="<?= e($s->twitter_url) ?>" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
      <?php if (!empty($s->linkedin_url)): ?><a href="<?= e($s->linkedin_url) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
    </div>
  </a>
</div>
