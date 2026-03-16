<?php
$startups = $startups ?? null;
$sortBy = $sortBy ?? 'upvotes';
$locationFilter = $locationFilter ?? null;
$browseLocations = $browseLocations ?? collect();
$productOfDayId = $productOfDayId ?? null;
$sortLabels = [
  'upvotes' => 'Upvotes',
  'views' => 'Views',
  'clicks' => 'Clicks',
  'mrr' => 'MRR',
  'revenue' => 'Revenue',
  'newest' => 'Newest',
];
$sortLabel = $sortLabels[$sortBy] ?? 'Upvotes';
?>
<section class="page-head">
  <div class="wrap">
    <h1>Leaderboard</h1>
    <p>Top startups. Sort by upvotes, views, clicks, MRR, or revenue.</p>
  </div>
</section>

<div class="wrap content-block">
  <?php if ($browseLocations->isNotEmpty()): ?>
  <div class="filters filters--categories" style="margin-bottom: 16px;">
    <a href="<?= e(url('/leaderboard?' . http_build_query(['sort' => $sortBy]))) ?>" class="pill<?= $locationFilter === null || $locationFilter === '' ? ' active' : '' ?>">All locations</a>
    <?php foreach ($browseLocations as $loc): ?>
    <a href="<?= e(url('/leaderboard?' . http_build_query(['sort' => $sortBy, 'location' => $loc->location]))) ?>" class="pill<?= $locationFilter === $loc->location ? ' active' : '' ?>"><?= e($loc->location) ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="leaderboard-wrap">
    <div class="leaderboard-header">
      <h2 class="leaderboard-title">Leaderboard</h2>
      <div class="leaderboard-filters">
        <label for="leaderboardSort" class="leaderboard-filter-label">Sort by</label>
        <select id="leaderboardSort" aria-label="Sort by">
          <option value="upvotes"<?= $sortBy === 'upvotes' ? ' selected' : '' ?>>Upvotes</option>
          <option value="views"<?= $sortBy === 'views' ? ' selected' : '' ?>>Views</option>
          <option value="clicks"<?= $sortBy === 'clicks' ? ' selected' : '' ?>>Clicks</option>
          <option value="mrr"<?= $sortBy === 'mrr' ? ' selected' : '' ?>>MRR</option>
          <option value="revenue"<?= $sortBy === 'revenue' ? ' selected' : '' ?>>Revenue</option>
          <option value="newest"<?= $sortBy === 'newest' ? ' selected' : '' ?>>Newest</option>
        </select>
      </div>
    </div>
    <table class="leaderboard-table">
      <thead>
        <tr>
          <th class="col-rank">#</th>
          <th class="col-startup">Startup</th>
          <th class="col-founder">Founder</th>
          <th class="col-metric"><?= e($sortLabel) ?></th>
          <th class="col-launched">Launched</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($startups && $startups->count() > 0): ?>
        <?php foreach ($startups as $index => $s):
          $rank = ($startups->currentPage() - 1) * $startups->perPage() + $index + 1;
          $logoPath = $s->logo_path ?? null;
          $logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
          $foundersDisplay = $s->founders_display ?? [];
          $founderName = count($foundersDisplay) > 0 ? implode(', ', array_column($foundersDisplay, 'name')) : ($s->founder_name ?? '—');
          if ($sortBy === 'mrr') { $metricVal = $s->mrr !== null && $s->mrr !== '' ? number_format((float)$s->mrr, 0) : '—'; }
          elseif ($sortBy === 'revenue') { $metricVal = $s->revenue !== null && $s->revenue !== '' ? number_format((float)$s->revenue, 0) : '—'; }
          elseif ($sortBy === 'views') { $metricVal = (int)($s->views ?? 0); }
          elseif ($sortBy === 'clicks') { $metricVal = (int)($s->clicks ?? 0); }
          elseif ($sortBy === 'newest') { $metricVal = $s->created_at ? $s->created_at->format('M Y') : '—'; }
          else { $metricVal = (int)$s->upvotes; }
        ?>
        <tr>
          <td class="col-rank">
            <?php if ($rank <= 3): ?><span class="leaderboard-rank-medal leaderboard-rank-medal--<?= $rank ?>"><?= $rank ?></span><?php else: ?><?= $rank ?><?php endif; ?>
          </td>
          <td class="col-startup">
            <a href="<?= e(url('/startup/' . $s->slug)) ?>" class="leaderboard-startup">
              <div class="leaderboard-startup-logo">
                <?php if ($logoPath): ?><img src="<?= e(asset($logoPath)) ?>" alt=""><?php else: ?><?= e($logoLetters) ?><?php endif; ?>
              </div>
              <div class="leaderboard-startup-info">
                <p class="leaderboard-startup-name"><?= e($s->name) ?><?php if ($productOfDayId && (int)$s->id === (int)$productOfDayId): ?> <span class="badge badge-product-of-day">Product of the day</span><?php endif; ?></p>
                <p class="leaderboard-startup-desc"><?= e($s->short_description) ?></p>
              </div>
            </a>
          </td>
          <td class="col-founder">
            <div class="leaderboard-founder">
              <div class="leaderboard-founder-avatars">
              <?php foreach (array_slice($foundersDisplay, 0, 4) as $fi => $fd):
                $fdPhoto = !empty($fd['photo_url']) ? $fd['photo_url'] : null;
                $fdInitials = \App\Models\Startup::founderInitials($fd['name'] ?? 'Founder');
                $fdIsExternal = $fdPhoto && (str_starts_with($fdPhoto, 'http://') || str_starts_with($fdPhoto, 'https://'));
                $fdSrc = $fdPhoto ? ($fdIsExternal ? $fdPhoto : asset($fdPhoto)) : null;
              ?>
                <div class="leaderboard-founder-avatar<?= $fi > 0 ? ' leaderboard-founder-avatar--overlap' : '' ?>" title="<?= e($fd['name'] ?? '') ?>">
                  <?php if ($fdSrc): ?>
                  <img src="<?= e($fdSrc) ?>" alt="<?= e($fd['name'] ?? '') ?>" onerror="this.style.display='none';this.nextElementSibling.style.display=''">
                  <span class="leaderboard-founder-initials" style="display:none"><?= e($fdInitials) ?></span>
                  <?php else: ?>
                  <span class="leaderboard-founder-initials"><?= e($fdInitials) ?></span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              </div>
              <span class="leaderboard-founder-name"><?= e($founderName) ?></span>
            </div>
          </td>
          <td class="col-metric"><?= e($metricVal) ?></td>
          <td class="col-launched"><?= $s->launch_date ? $s->launch_date->format('Y') : '—' ?></td>
        </tr>
        <?php if ($rank % 10 === 0): ?>
        <tr class="leaderboard-ad-row">
          <td colspan="5" style="text-align:center; padding:12px 0;">
            <div class="MainAdverTiseMentDiv" data-publisher="eyJpdiI6ImwxcHg2Wm1oOEZTRENvbzV1OVFyTnc9PSIsInZhbHVlIjoiZkVpQ0FDclU4Q0hvcXFCK3I0QzdFdz09IiwibWFjIjoiZjZkYjJhMmE5OTIzZTZlODg0OTg4YzNiZDVmMjUzYjA3MzAwN2Q5MTIwNmY3OTczOTZmNTg0ZDdiZmY4M2E3NyIsInRhZyI6IiJ9" data-adsize="728x90"></div>
            <script class="adScriptClass" src="https://zimadsense.com/assets/ads/ad.js"></script>
          </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="5" style="padding: 40px; text-align: center; color: var(--text-muted);">No startups yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a>.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <?php if ($startups && $startups->hasPages()): ?>
    <div class="pagination-container">
      <?= $startups->withQueryString()->links() ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('leaderboardSort')?.addEventListener('change', function() {
  var url = new URL(window.location.href);
  url.searchParams.set('sort', this.value);
  window.location.href = url.toString();
});
</script>
<?php if (auth()->check() && !auth()->user()->isPro()): ?>
<script>
(function() {
  setTimeout(function() {
    if (typeof edenPromoToast === 'function') {
      edenPromoToast({ key: 'pro_leaderboard', message: 'Pro founders get detailed analytics and stand out. See what you\'re missing.', ctaText: 'See Pro', ctaHref: typeof edenPricingUrl !== 'undefined' ? edenPricingUrl : '<?= e(url('/pricing')) ?>' });
    }
  }, 2000);
})();
</script>
<?php endif; ?>
