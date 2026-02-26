<section class="hero">
  <div class="wrap">
    <h1>Discover the next wave of startups</h1>
    <p>Explore, search, and connect. One directory. Zero noise.</p>
    <div class="hero-actions">
      <div class="search-wrap hero-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" class="search-input" placeholder="Search startups, founders, categories…" aria-label="Search" id="homeSearch">
      </div>
      <a href="<?= e(url('/submit')) ?>" class="btn btn-primary btn-add"><i class="fa-solid fa-plus" aria-hidden="true"></i> Submit your startup</a>
    </div>
    <nav class="hero-quick-nav" aria-label="Quick links">
      <a href="<?= e(url('/launching-today')) ?>">Launching today</a>
      <span class="hero-quick-nav-sep" aria-hidden="true">·</span>
      <a href="<?= e(url('/leaderboard')) ?>">Leaderboard</a>
      <span class="hero-quick-nav-sep" aria-hidden="true">·</span>
      <a href="<?= e(url('/categories')) ?>">Categories</a>
      <span class="hero-quick-nav-sep" aria-hidden="true">·</span>
      <a href="<?= e(url('/submit')) ?>">Submit</a>
    </nav>
    <?php $browseCategories = $browseCategories ?? []; ?>
    <?php if (count($browseCategories) > 0): ?>
    <div class="hero-categories" id="heroCategories">
      <h2 class="hero-categories-title">Browse by category</h2>
      <div class="filters filters--categories">
        <?php $categoryFilter = $categoryFilter ?? null; ?>
        <a href="<?= e(url('/')) ?>" class="pill<?= $categoryFilter === null || $categoryFilter === '' ? ' active' : '' ?>">All</a>
        <?php foreach ($browseCategories as $cat): ?>
        <a href="<?= e(url('/?category=' . urlencode($cat->name))) ?>" class="pill<?= $categoryFilter === $cat->name ? ' active' : '' ?>"><?= e($cat->name) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<div class="wrap">
  <?php $launchingToday = $launchingToday ?? collect(); ?>
  <?php if ($launchingToday->isNotEmpty()): ?>
  <section class="section-block" aria-labelledby="products-launching-heading">
    <header class="section-header">
      <div>
        <h2 id="products-launching-heading" class="section-heading">Products launching today</h2>
        <p class="section-sub">Fresh launches. Be the first to discover them.</p>
      </div>
      <a href="<?= e(url('/launching-today')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($launchingToday as $startup): $badgeLabel = 'Launch'; include __DIR__ . '/_startup-card-deal.php'; endforeach; ?>
    </div>
    <p class="section-browse-all"><a href="<?= e(url('/launching-today')) ?>">Browse all on Launching today</a></p>
  </section>
  <?php endif; ?>

  <?php $featuredProducts = $featuredProducts ?? collect(); ?>
  <?php if ($featuredProducts->isNotEmpty()): ?>
  <section class="section-block" aria-labelledby="featured-heading">
    <header class="section-header">
      <div>
        <h2 id="featured-heading" class="section-heading">Featured products</h2>
        <p class="section-sub">Hand-picked startups to watch.</p>
      </div>
      <a href="<?= e(url('/?featured=1')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($featuredProducts as $startup): $badgeLabel = 'Featured'; include __DIR__ . '/_startup-card-deal.php'; endforeach; ?>
    </div>
    <p class="section-browse-all"><a href="<?= e(url('/?featured=1')) ?>">Browse all featured startups</a></p>
  </section>
  <?php endif; ?>

  <?php $topPerforming = $topPerforming ?? collect(); ?>
  <section class="section-block" aria-labelledby="top-performing-heading">
    <header class="section-header">
      <div>
        <h2 id="top-performing-heading" class="section-heading">Top performing products</h2>
        <p class="section-sub">Most upvoted this week.</p>
      </div>
      <a href="<?= e(url('/leaderboard')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <?php if ($topPerforming->isNotEmpty()): ?>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($topPerforming as $startup): $badgeLabel = 'Top'; include __DIR__ . '/_startup-card-deal.php'; endforeach; ?>
    </div>
    <p class="section-browse-all"><a href="<?= e(url('/leaderboard')) ?>">Browse all on Leaderboard</a></p>
    <?php else: ?>
    <p class="section-empty">No startups yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a>.</p>
    <?php endif; ?>
  </section>

  <?php $justListed = $justListed ?? collect(); ?>
  <?php if ($justListed->isNotEmpty()): ?>
  <section class="section-block" aria-labelledby="just-listed-heading">
    <header class="section-header">
      <div>
        <h2 id="just-listed-heading" class="section-heading">Just listed</h2>
        <p class="section-sub">Newest additions to the directory.</p>
      </div>
      <a href="<?= e(url('/?sort=newest')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($justListed as $startup): $badgeLabel = 'New'; include __DIR__ . '/_startup-card-deal.php'; endforeach; ?>
    </div>
    <p class="section-browse-all"><a href="<?= e(url('/?sort=newest')) ?>">Browse all just listed</a></p>
  </section>
  <?php endif; ?>

  <?php $leaderboardPreview = $leaderboardPreview ?? null; ?>
  <?php if ($leaderboardPreview && count($leaderboardPreview->items()) > 0): ?>
  <section class="section-block" aria-labelledby="leaderboard-heading">
    <header class="section-header">
      <h2 id="leaderboard-heading" class="section-heading">Leaderboard</h2>
      <a href="<?= e(url('/leaderboard')) ?>" class="section-link-all">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>
    <div class="leaderboard-wrap">
      <table class="leaderboard-table">
        <thead>
          <tr>
            <th class="col-rank">#</th>
            <th class="col-startup">Startup</th>
            <th class="col-founder">Founder</th>
            <th class="col-upvotes">Upvotes</th>
            <th class="col-launched">Launched</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $foundersDisplay = null;
          foreach ($leaderboardPreview as $index => $s):
            $rank = $leaderboardPreview->firstItem() + $index;
            $logoPath = $s->logo_path ?? null;
            $logoLetters = $s->logo_letters ?? strtoupper(mb_substr($s->name, 0, 2));
            $foundersDisplay = $s->founders_display ?? [];
            $founderName = count($foundersDisplay) > 0 ? implode(', ', array_column($foundersDisplay, 'name')) : ($s->founder_name ?? '—');
            $founderPhoto = count($foundersDisplay) > 0 && !empty($foundersDisplay[0]['photo_url']) ? $foundersDisplay[0]['photo_url'] : null;
            $founderInitials = count($foundersDisplay) > 0 ? \App\Models\Startup::founderInitials($foundersDisplay[0]['name']) : '?';
          ?>
          <tr>
            <td class="col-rank"><?= (int)$rank ?></td>
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
            <td class="col-upvotes"><?= (int)$s->upvotes ?></td>
            <td class="col-launched"><?= $s->launch_date ? $s->launch_date->format('Y') : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
  <?php endif; ?>

  <section class="section-block" aria-labelledby="browse-category-heading">
    <h2 id="browse-category-heading" class="section-heading section-heading--center">Browse by category</h2>
    <div class="filters filters--categories" id="categories">
      <?php $categoryFilter = $categoryFilter ?? null; $browseCategories = $browseCategories ?? []; ?>
      <a href="<?= e(url('/')) ?>" class="pill<?= $categoryFilter === null || $categoryFilter === '' ? ' active' : '' ?>">All</a>
      <?php foreach ($browseCategories as $cat): ?>
      <a href="<?= e(url('/?category=' . urlencode($cat->name))) ?>" class="pill<?= $categoryFilter === $cat->name ? ' active' : '' ?>"><?= e($cat->name) ?></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section-block" aria-labelledby="startups-heading">
    <header class="section-header">
      <h2 id="startups-heading" class="section-heading">Startups</h2>
      <a href="<?= e(url('/launching-today')) ?>" class="section-link-all">Launching today <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
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
      <p class="section-empty">No startups yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a>.</p>
      <?php endif; ?>
    </div>
  </section>

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
