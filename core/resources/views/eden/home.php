<section class="hero">
  <div class="hero-bg" aria-hidden="true">
    <span class="hero-bg-orb hero-bg-orb--1"></span>
    <span class="hero-bg-orb hero-bg-orb--2"></span>
    <span class="hero-bg-orb hero-bg-orb--3"></span>
    <span class="hero-bg-grid"></span>
  </div>
  <div class="wrap">
    <h1>Discover the next wave of startups</h1>
    <p>Explore, search, and connect. One directory. Zero noise.</p>
    <div class="hero-actions">
      <form action="<?= e(url('/')) ?>" method="get" class="search-wrap hero-search" role="search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" name="q" class="search-input" placeholder="Search startups, founders, categories…" aria-label="Search" id="homeSearch" value="<?= e($searchQuery ?? '') ?>">
        <?php if (isset($categoryFilter) && $categoryFilter !== null && $categoryFilter !== ''): ?><input type="hidden" name="category" value="<?= e($categoryFilter) ?>"><?php endif; ?>
        <?php if (isset($locationFilter) && $locationFilter !== null && $locationFilter !== ''): ?><input type="hidden" name="location" value="<?= e($locationFilter) ?>"><?php endif; ?>
      </form>
      <a href="<?= e(url('/submit')) ?>" class="btn btn-primary btn-add"><i class="fa-solid fa-plus" aria-hidden="true"></i> Submit your startup</a>
    </div>
    <?php
    $showTrustedByBlock = $showTrustedByBlock ?? false;
    $featuredFounders = $featuredFounders ?? collect();
    if ($showTrustedByBlock && $featuredFounders->isNotEmpty()):
    ?>
    <div class="hero-trusted-by" style="display:flex;flex-direction:row;align-items:center;justify-content:center;gap:12px;margin-top:24px" aria-label="Trusted by founders">
      <div class="hero-trusted-by-avatars" style="display:flex;align-items:center">
        <?php foreach ($featuredFounders as $founder):
          $photoUrl = !empty(trim($founder->hero_photo_url ?? '')) ? $founder->hero_photo_url : null;
          $linkedinUrl = !empty(trim($founder->hero_linkedin_url ?? '')) ? $founder->hero_linkedin_url : null;
          $name = $founder->name ?? 'Founder';
          $initials = preg_match('/\S+\s+(\S)/', $name, $m) ? strtoupper(mb_substr($name, 0, 1) . $m[1]) : strtoupper(mb_substr($name, 0, 2));
          if ($initials === '') $initials = '?';
        ?>
        <?php
          $isExternal = $photoUrl && (str_starts_with($photoUrl, 'http://') || str_starts_with($photoUrl, 'https://'));
          $imgSrc = $photoUrl ? ($isExternal ? $photoUrl : asset($photoUrl)) : null;
        ?>
        <?php if ($linkedinUrl): ?>
        <a href="<?= e($linkedinUrl) ?>" target="_blank" rel="noopener" class="hero-trusted-by-avatar" title="<?= e($name) ?>">
          <?php if ($imgSrc): ?>
          <img src="<?= e($imgSrc) ?>" alt="<?= e($name) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display=''">
          <span class="hero-trusted-by-initials" style="display:none"><?= e($initials) ?></span>
          <?php else: ?>
          <span class="hero-trusted-by-initials"><?= e($initials) ?></span>
          <?php endif; ?>
        </a>
        <?php else: ?>
        <div class="hero-trusted-by-avatar" title="<?= e($name) ?>">
          <?php if ($imgSrc): ?>
          <img src="<?= e($imgSrc) ?>" alt="<?= e($name) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display=''">
          <span class="hero-trusted-by-initials" style="display:none"><?= e($initials) ?></span>
          <?php else: ?>
          <span class="hero-trusted-by-initials"><?= e($initials) ?></span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <p class="hero-trusted-by-text" style="margin:0;flex:none">100+ founders</p>
    </div>
    <?php endif; ?>
    <nav class="hero-quick-nav" aria-label="Quick links">
      <a href="<?= e(url('/launching-today')) ?>">Launching today</a>
      <span class="hero-quick-nav-sep" aria-hidden="true">·</span>
      <a href="<?= e(url('/leaderboard')) ?>">Leaderboard</a>
      <span class="hero-quick-nav-sep" aria-hidden="true">·</span>
      <a href="<?= e(url('/categories')) ?>">Categories</a>
      <span class="hero-quick-nav-sep" aria-hidden="true">·</span>
      <a href="<?= e(url('/submit')) ?>">Submit</a>
    </nav>
    <?php $browseCategories = $browseCategories ?? []; $browseLocations = $browseLocations ?? []; ?>
    <?php
    $categoryFilter = $categoryFilter ?? null;
    $locationFilter = $locationFilter ?? null;
    $featuredOnly = $featuredOnly ?? false;
    $sortNewest = $sortNewest ?? false;
    $searchQuery = $searchQuery ?? null;
    $baseQuery = array_filter(['q' => $searchQuery && trim($searchQuery) !== '' ? trim($searchQuery) : null, 'featured' => $featuredOnly ? '1' : null, 'sort' => $sortNewest ? 'newest' : null, 'location' => $locationFilter ?: null]);
    ?>
    <?php if (count($browseCategories) > 0): ?>
    <div class="hero-categories" id="heroCategories">
      <h2 class="hero-categories-title">Browse by category</h2>
      <div class="filters filters--categories">
        <?php $urlAll = url('/') . ($baseQuery ? '?' . http_build_query($baseQuery) : ''); ?>
        <a href="<?= e($urlAll) ?>" class="pill<?= $categoryFilter === null || $categoryFilter === '' ? ' active' : '' ?>">All</a>
        <?php foreach ($browseCategories as $cat): ?>
        <?php $query = array_merge($baseQuery, ['category' => $cat->name]); ?>
        <a href="<?= e(url('/') . '?' . http_build_query($query)) ?>" class="pill<?= $categoryFilter === $cat->name ? ' active' : '' ?>"><?= e($cat->name) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php if (count($browseLocations) > 0): ?>
    <div class="hero-categories" id="heroLocations" style="margin-top: 12px;">
      <h2 class="hero-categories-title">Location</h2>
      <div class="filters filters--categories">
        <a href="<?= e(url('/') . ($baseQuery ? '?' . http_build_query($baseQuery) : '')) ?>" class="pill<?= $locationFilter === null || $locationFilter === '' ? ' active' : '' ?>">All</a>
        <?php foreach ($browseLocations as $loc): ?>
        <?php $query = array_merge($baseQuery, ['location' => $loc->name]); ?>
        <a href="<?= e(url('/') . '?' . http_build_query($query)) ?>" class="pill<?= $locationFilter === $loc->name ? ' active' : '' ?>"><?= e($loc->name) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<div style="display:flex; justify-content:center; padding:16px 0;">
  <div class="MainAdverTiseMentDiv" data-publisher="eyJpdiI6InpsbjBkRVNsSTg0YVpndEFVdCt1Mmc9PSIsInZhbHVlIjoiUnJTUHc3TzRpT3UzVWxZR3ozL0xidz09IiwibWFjIjoiMTk2MTE2YTk1YmUxZmRlZGFlMzRhNmQ2ZGRmY2E5MDBhZWQwYjk4Mjc2MDhiNmZjNmJlYTM2MjAyZDdiMDRjYiIsInRhZyI6IiJ9" data-adsize="970x90"></div>
  <script class="adScriptClass" src="https://zimadsense.com/assets/ads/ad.js"></script>
</div>

<div class="wrap">
  <?php $sortNewest = $sortNewest ?? false; ?>
  <?php $launchingToday = $launchingToday ?? collect(); ?>
  <?php if (!$sortNewest && $launchingToday->isNotEmpty()): ?>
  <section class="section-block" aria-labelledby="products-launching-heading">
    <header class="section-header">
      <div>
        <h2 id="products-launching-heading" class="section-heading">Products launching today</h2>
        <p class="section-sub">Fresh launches. Be the first to discover them.</p>
      </div>
      <a href="<?= e(url('/launching-today')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($launchingToday as $startup):
        $rank = null;
        $showRank = false;
        $cardVariant = 'row';
        include __DIR__ . '/_startup-card.php';
      endforeach; ?>
    </div>
    <p class="section-browse-all"><a href="<?= e(url('/launching-today')) ?>">Browse all on Launching today</a></p>
  </section>

  <?php $homeAd = $homeAd ?? null; ?>
  <div class="home-ad-spot" style="margin: 24px 0;">
    <?php if ($homeAd): ?>
    <?php
      $homeAdPath = $homeAd->image_path ?? '';
      $homeAdIsExternal = is_string($homeAdPath) && ($homeAdPath !== '') && (str_starts_with($homeAdPath, 'http://') || str_starts_with($homeAdPath, 'https://'));
      $homeAdSrc = $homeAdIsExternal ? $homeAdPath : asset($homeAdPath);
    ?>
    <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border, #e2e8f0); background: #0f172a;">
      <a href="<?= e($homeAd->target_url) ?>" target="_blank" rel="noopener noreferrer" style="display: block;">
        <img src="<?= e($homeAdSrc) ?>" alt="Sponsored ad" style="display: block; width: 100%; max-width: 728px; height: auto; margin: 0 auto;">
      </a>
    </div>
    <?php else: ?>
    <div style="border-radius: 8px; border: 1px dashed var(--border, #e2e8f0); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; background: rgba(15,23,42,0.6);">
      <div>
        <p style="margin: 0 0 4px; font-weight: 600;">Ad spot available</p>
        <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted, #94a3b8);">
          Place your 728x90 banner here on the Eden homepage.
        </p>
      </div>
      <div>
        <a href="<?= e(url('/advertise/blog')) ?>" class="btn btn-primary">Buy this ad spot</a>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?php endif; ?>

  <?php $trendingStartups = $trendingStartups ?? collect(); ?>
  <?php if (!$sortNewest && $trendingStartups->isNotEmpty()): ?>
  <section class="section-block" aria-labelledby="trending-heading">
    <header class="section-header">
      <div>
        <h2 id="trending-heading" class="section-heading">Hot this week</h2>
        <p class="section-sub">Most upvoted in the last 7 days.</p>
      </div>
    </header>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($trendingStartups as $startup):
        $rank = null;
        $showRank = false;
        $cardVariant = 'row';
        include __DIR__ . '/_startup-card.php';
      endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php $featuredProducts = $featuredProducts ?? collect(); ?>
  <?php if (!$sortNewest && $featuredProducts->isNotEmpty()): ?>
  <section class="section-block" aria-labelledby="featured-heading">
    <header class="section-header">
      <div>
        <h2 id="featured-heading" class="section-heading">Featured products</h2>
        <p class="section-sub">Hand-picked startups to watch.</p>
      </div>
      <a href="<?= e(url('/?featured=1')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($featuredProducts as $startup):
        $rank = null;
        $showRank = false;
        $cardVariant = 'row';
        include __DIR__ . '/_startup-card.php';
      endforeach; ?>
    </div>
    <p class="section-browse-all"><a href="<?= e(url('/?featured=1')) ?>">Browse all featured startups</a></p>
  </section>
  <?php endif; ?>

  <?php
  $leaderboardPreview = $leaderboardPreview ?? null;
  $leaderboardSort = $leaderboardSort ?? 'upvotes';
  $leaderboardSortLabels = ['upvotes' => 'Upvotes', 'views' => 'Views', 'clicks' => 'Clicks', 'mrr' => 'MRR', 'revenue' => 'Revenue', 'newest' => 'Newest'];
  ?>
  <?php if (!$sortNewest && $leaderboardPreview && count($leaderboardPreview->items()) > 0): ?>
  <section class="section-block" aria-labelledby="leaderboard-heading">
    <div class="leaderboard-wrap">
      <div class="leaderboard-header">
        <h2 id="leaderboard-heading" class="leaderboard-title">Leaderboard</h2>
        <div class="leaderboard-header-actions" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
          <label for="home-leaderboard-sort" class="leaderboard-filter-label" style="font-size: 0.875rem; color: var(--text-muted, #64748b);">Sort by</label>
          <select id="home-leaderboard-sort" aria-label="Sort leaderboard by" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border, #e2e8f0); font-size: 0.875rem;">
            <?php foreach ($leaderboardSortLabels as $value => $label): ?>
            <option value="<?= e($value) ?>"<?= $leaderboardSort === $value ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <a href="<?= e(url('/leaderboard?' . http_build_query(['sort' => $leaderboardSort]))) ?>" class="leaderboard-view-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
      </div>
      <table class="leaderboard-table">
        <thead>
          <tr>
            <th class="col-rank">#</th>
            <th class="col-startup">Startup</th>
            <th class="col-founder">Founder</th>
            <th class="col-upvotes"><?= e($leaderboardSortLabels[$leaderboardSort] ?? 'Upvotes') ?></th>
            <th class="col-launched">Launched</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $foundersDisplay = null;
          foreach ($leaderboardPreview as $index => $s):
            $rank = (int)($leaderboardPreview->firstItem() + $index);
            $logoPath = $s->logo_path ?? null;
            $logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
            $foundersDisplay = $s->founders_display ?? [];
            $founderName = count($foundersDisplay) > 0 ? implode(', ', array_column($foundersDisplay, 'name')) : ($s->founder_name ?? '—');
            $founderPhoto = count($foundersDisplay) > 0 && !empty($foundersDisplay[0]['photo_url']) ? $foundersDisplay[0]['photo_url'] : null;
            $founderInitials = count($foundersDisplay) > 0 ? \App\Models\Startup::founderInitials($foundersDisplay[0]['name']) : '?';
            if ($leaderboardSort === 'mrr') { $metricVal = $s->mrr !== null && $s->mrr !== '' ? number_format((float)$s->mrr, 0) : '—'; }
            elseif ($leaderboardSort === 'revenue') { $metricVal = $s->revenue !== null && $s->revenue !== '' ? number_format((float)$s->revenue, 0) : '—'; }
            elseif ($leaderboardSort === 'views') { $metricVal = (int)($s->views ?? 0); }
            elseif ($leaderboardSort === 'clicks') { $metricVal = (int)($s->clicks ?? 0); }
            elseif ($leaderboardSort === 'newest') { $metricVal = $s->created_at ? $s->created_at->format('M Y') : '—'; }
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
                  <p class="leaderboard-startup-name"><?= e($s->name) ?><?php if (!empty($productOfDayId) && (int)$s->id === (int)$productOfDayId): ?> <span class="badge badge-product-of-day">Product of the day</span><?php endif; ?></p>
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
            <td class="col-upvotes"><?= $metricVal ?></td>
            <td class="col-launched"><?= $s->launch_date ? $s->launch_date->format('Y') : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <script>
    (function() {
      var sel = document.getElementById('home-leaderboard-sort');
      if (sel) sel.addEventListener('change', function() {
        var params = new URLSearchParams(window.location.search);
        params.set('leaderboard_sort', this.value);
        window.location = '<?= e(url('/')) ?>?' + params.toString();
      });
    })();
    </script>
  </section>
  <?php endif; ?>

  <div style="display:flex; justify-content:center; padding:16px 0;">
    <div class="MainAdverTiseMentDiv" data-publisher="eyJpdiI6InpsbjBkRVNsSTg0YVpndEFVdCt1Mmc9PSIsInZhbHVlIjoiUnJTUHc3TzRpT3UzVWxZR3ozL0xidz09IiwibWFjIjoiMTk2MTE2YTk1YmUxZmRlZGFlMzRhNmQ2ZGRmY2E5MDBhZWQwYjk4Mjc2MDhiNmZjNmJlYTM2MjAyZDdiMDRjYiIsInRhZyI6IiJ9" data-adsize="970x90"></div>
    <script class="adScriptClass" src="https://zimadsense.com/assets/ads/ad.js"></script>
  </div>

  <?php $justListed = $justListed ?? collect(); ?>
  <?php if (!$sortNewest && $justListed->isNotEmpty()): ?>
  <section class="section-block" aria-labelledby="just-listed-heading">
    <header class="section-header">
      <div>
        <h2 id="just-listed-heading" class="section-heading">Just listed</h2>
        <p class="section-sub">Newest additions to the directory.</p>
      </div>
      <a href="<?= e(url('/?sort=newest')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($justListed as $startup):
        $rank = null;
        $showRank = false;
        $cardVariant = 'row';
        include __DIR__ . '/_startup-card.php';
      endforeach; ?>
    </div>
    <p class="section-browse-all"><a href="<?= e(url('/?sort=newest')) ?>">Browse all just listed</a></p>
  </section>
  <?php endif; ?>

  <section class="section-block" aria-labelledby="startups-heading">
    <header class="section-header">
      <h2 id="startups-heading" class="section-heading"><?= ($searchQuery ?? '') !== '' ? 'Search results for “' . e($searchQuery) . '”' : (($sortNewest ?? false) ? 'Just listed' : (($featuredOnly ?? false) ? 'Featured startups' : 'All Startups')) ?></h2>
      <?php if (($searchQuery ?? '') !== ''): ?>
      <a href="<?= e(url('/')) ?>" class="section-link-all">Clear search <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <?php elseif ($sortNewest ?? false): ?>
      <a href="<?= e(url('/leaderboard?sort=newest')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <?php else: ?>
      <a href="<?= e(url('/launching-today')) ?>" class="section-link-all">Launching today <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <?php endif; ?>
    </header>
    <div class="startup-list" id="startups">
      <?php
      $allStartups = $allStartups ?? collect();
      foreach ($allStartups as $startup):
        $rank = null;
        $showRank = false;
        $cardVariant = null;
        include __DIR__ . '/_startup-card.php';
      endforeach;
      ?>
      <?php if ($allStartups->isEmpty()): ?>
      <p class="section-empty"><?= ($searchQuery ?? '') !== '' ? 'No startups match your search. Try different keywords.' : 'No startups yet. <a href="' . e(url('/submit')) . '">Submit your startup</a>.' ?></p>
      <?php endif; ?>
    </div>
    <?php if (isset($searchResults) && $searchResults && $searchResults->hasPages()): ?>
    <div class="section-pagination" style="margin-top: 24px;"><?= $searchResults->withQueryString()->links() ?></div>
    <?php endif; ?>
  </section>

  <?php $homeBottomAd = $homeBottomAd ?? null; ?>
  <div class="home-ad-spot home-ad-spot--bottom" style="margin: 24px 0 32px;">
    <?php if ($homeBottomAd): ?>
    <?php
      $homeBottomAdPath = $homeBottomAd->image_path ?? '';
      $homeBottomAdIsExternal = is_string($homeBottomAdPath) && ($homeBottomAdPath !== '') && (str_starts_with($homeBottomAdPath, 'http://') || str_starts_with($homeBottomAdPath, 'https://'));
      $homeBottomAdSrc = $homeBottomAdIsExternal ? $homeBottomAdPath : asset($homeBottomAdPath);
    ?>
    <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border, #e2e8f0); background: #0f172a;">
      <a href="<?= e($homeBottomAd->target_url) ?>" target="_blank" rel="noopener noreferrer" style="display: block;">
        <img src="<?= e($homeBottomAdSrc) ?>" alt="Sponsored ad" style="display: block; width: 100%; max-width: 728px; height: auto; margin: 0 auto;">
      </a>
    </div>
    <?php else: ?>
    <div style="border-radius: 8px; border: 1px dashed var(--border, #e2e8f0); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; background: rgba(15,23,42,0.6);">
      <div>
        <p style="margin: 0 0 4px; font-weight: 600;">Ad spot for sale</p>
        <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted, #94a3b8);">
          Place your 728x90 banner right above this call to action.
        </p>
      </div>
      <div>
        <a href="<?= e(url('/advertise/blog')) ?>" class="btn btn-primary">Buy this ad spot</a>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="cta-strip" id="submit">
    <h3>Launching something?</h3>
    <p>Get your startup in front of investors and customers. Submit in under 2 minutes.</p>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
    <a href="<?= e(url('/about')) ?>" class="btn btn-ghost">View guidelines</a>
  </div>

  <div class="newsletter">
    <form action="<?= e(url('/subscribe')) ?>" method="POST" class="newsletter-form">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
      <input type="email" name="email" placeholder="Your email" aria-label="Email" required>
      <button type="submit" class="btn btn-primary">Subscribe</button>
    </form>
    <p class="newsletter-note">Stay updated on new startups. No spam.</p>
  </div>
</div>
