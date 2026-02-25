<section class="hero">
  <div class="wrap">
    <h1>Discover the next wave of startups</h1>
    <p>Explore, search, and connect with innovative companies. One directory. Zero noise.</p>
    <div class="search-wrap">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" class="search-input" placeholder="Search startups, tags, or categories…" aria-label="Search">
    </div>
  </div>
</section>

<div class="wrap">
  <div class="launch-strip">
    <div class="wrap launch-strip-inner">
      <div>
        <h2>Startups launching today</h2>
        <p>Fresh launches. Be the first to discover them.</p>
      </div>
      <a href="<?= e(url('/launching-today')) ?>" class="btn btn-primary">View all</a>
    </div>
  </div>

  <section class="product-of-day">
    <h2 class="section-title">Product of the day</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">Top 5 by upvotes today.</p>
    <div class="startup-list">
      <?php
      $productOfDay = $productOfDay ?? collect();
      foreach ($productOfDay as $index => $startup):
        $rank = $index + 1;
        $showRank = true;
        include __DIR__ . '/_startup-card.php';
      endforeach;
      ?>
      <?php if ($productOfDay->isEmpty()): ?>
      <p class="text-muted">No startups yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a>.</p>
      <?php endif; ?>
    </div>
  </section>

  <div class="filters" id="categories">
    <span>Category:</span>
    <?php $categoryFilter = $categoryFilter ?? null; ?>
    <a href="<?= e(url('/')) ?>" class="pill<?= $categoryFilter === null || $categoryFilter === '' ? ' active' : '' ?>">All</a>
    <?php foreach ($categories ?? [] as $cat): ?>
    <a href="<?= e(url('/?category=' . urlencode($cat->category))) ?>" class="pill<?= $categoryFilter === $cat->category ? ' active' : '' ?>"><?= e($cat->category) ?></a>
    <?php endforeach; ?>
  </div>

  <h2 class="section-title">Startups <a href="<?= e(url('/launching-today')) ?>" class="section-link">Launching today →</a></h2>
  <div class="startup-list" id="startups">
    <?php
    $allStartups = $allStartups ?? collect();
    foreach ($allStartups as $startup):
      $rank = null;
      $showRank = false;
      include __DIR__ . '/_startup-card.php';
    endforeach;
    ?>
    <?php if ($allStartups->isEmpty()): ?>
    <p class="text-muted">No startups yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a>.</p>
    <?php endif; ?>
  </div>

  <div class="cta-strip" id="submit">
    <h3>Launching something?</h3>
    <p>Get your startup in front of investors and customers. Submit in under 2 minutes.</p>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
    <a href="<?= e(url('/about')) ?>" class="btn btn-ghost">View guidelines</a>
  </div>

  <div class="newsletter">
    <input type="email" placeholder="Your email" aria-label="Email">
    <button type="button" class="btn btn-primary">Subscribe</button>
    <p class="newsletter-note">Weekly digest of new startups. No spam.</p>
  </div>
</div>
