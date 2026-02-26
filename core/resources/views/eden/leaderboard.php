<?php
$startups = $startups ?? null;
$sortBy = $sortBy ?? 'upvotes';
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
          $founderPhoto = count($foundersDisplay) > 0 && !empty($foundersDisplay[0]['photo_url']) ? $foundersDisplay[0]['photo_url'] : null;
          $founderInitials = count($foundersDisplay) > 0 ? \App\Models\Startup::founderInitials($foundersDisplay[0]['name']) : '?';
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
                <p class="leaderboard-startup-name"><?= e($s->name) ?></p>
                <p class="leaderboard-startup-desc"><?= e($s->short_description) ?></p>
              </div>
            </a>
          </td>
          <td class="col-founder">
            <div class="leaderboard-founder">
              <?php if ($founderPhoto): ?>
              <div class="leaderboard-founder-avatar"><img src="<?= e(asset($founderPhoto)) ?>" alt=""></div>
              <?php else: ?>
              <div class="leaderboard-founder-avatar"><span class="leaderboard-founder-initials"><?= e($founderInitials) ?></span></div>
              <?php endif; ?>
              <span class="leaderboard-founder-name"><?= e($founderName) ?></span>
            </div>
          </td>
          <td class="col-metric"><?= e($metricVal) ?></td>
          <td class="col-launched"><?= $s->launch_date ? $s->launch_date->format('Y') : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="5" style="padding: 40px; text-align: center; color: var(--text-muted);">No startups yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a>.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <?php if ($startups && $startups->hasPages()): ?>
    <div class="leaderboard-pagination">
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
