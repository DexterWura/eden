<section class="hero">
  <div class="wrap">
    <h1>Discover the next wave of startups</h1>
    <p>Explore, search, and connect with innovative companies. One directory. Zero noise.</p>
    <div class="hero-actions">
      <div class="search-wrap hero-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" class="search-input" placeholder="Search startups, founders, categories…" aria-label="Search" id="homeSearch">
      </div>
      <a href="<?= e(url('/submit')) ?>" class="btn btn-primary btn-add"><i class="fa-solid fa-plus" aria-hidden="true"></i> Submit your startup</a>
    </div>
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
      <?php foreach ($launchingToday as $startup): $rank = null; $showRank = false; $cardVariant = 'row'; include __DIR__ . '/_startup-card.php'; endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="section-block product-of-day" aria-labelledby="product-of-day-heading">
    <header class="section-header">
      <div>
        <h2 id="product-of-day-heading" class="section-heading">Product of the day</h2>
        <p class="section-sub">Top 5 by upvotes today.</p>
      </div>
    </header>
    <?php $productOfDay = $productOfDay ?? collect(); ?>
    <?php if ($productOfDay->isNotEmpty()): ?>
    <div class="section-cards-row" tabindex="0">
      <?php foreach ($productOfDay as $index => $startup): $rank = $index + 1; $showRank = true; $cardVariant = 'row'; include __DIR__ . '/_startup-card.php'; endforeach; ?>
    </div>
    <?php else: ?>
    <p class="section-empty">No startups yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a>.</p>
    <?php endif; ?>
  </section>

  <section class="section-block" aria-labelledby="browse-category-heading">
    <h2 id="browse-category-heading" class="section-heading section-heading--center">Browse by category</h2>
    <div class="filters filters--categories" id="categories">
      <?php $categoryFilter = $categoryFilter ?? null; ?>
      <a href="<?= e(url('/')) ?>" class="pill<?= $categoryFilter === null || $categoryFilter === '' ? ' active' : '' ?>">All</a>
      <?php foreach ($categories ?? [] as $cat): ?>
      <a href="<?= e(url('/?category=' . urlencode($cat->category))) ?>" class="pill<?= $categoryFilter === $cat->category ? ' active' : '' ?>"><?= e($cat->category) ?></a>
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
