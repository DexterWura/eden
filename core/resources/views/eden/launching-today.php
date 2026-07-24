<section class="page-head">
  <div class="wrap">
    <h1>Apps launching today</h1>
    <p>Fresh launches. Upvote your favourites and be the first to discover them.</p>
  </div>
</section>

<div class="wrap">
  <?php $launchingAd = $launchingAd ?? null; ?>
  <div style="margin-bottom: 20px;">
    <?php
      $ad = $launchingAd;
      $buyUrl = url('/advertise/launching');
      $emptyTitle = 'Launching today — ad spot';
      $emptyCopy = '728×90 banner for visitors browsing today’s launches.';
      $maxWidth = 728;
      include __DIR__ . '/partials/ad-spot.php';
    ?>
  </div>
  <h2 class="section-title">Today's launches</h2>
  <div class="startup-list">
    <?php
    $startups = $startups ?? collect();
    foreach ($startups as $startup):
      $rank = null;
      $showRank = false;
      include __DIR__ . '/_startup-card.php';
    endforeach;
    ?>
    <?php if ($startups->isEmpty()): ?>
    <p class="text-muted">No apps launching today. <a href="<?= e(url('/submit')) ?>">Submit your app</a>.</p>
    <?php endif; ?>
  </div>

  <div class="cta-strip">
    <h3>Launching soon?</h3>
    <p>List your app and get featured on this page. Upvotes help you reach Product of the day at the end of each day.</p>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your app</a>
  </div>

  <div class="newsletter">
    <form action="<?= e(url('/subscribe')) ?>" method="POST" class="newsletter-form">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
      <input type="email" name="email" placeholder="Your email" aria-label="Email" required>
      <button type="submit" class="btn btn-primary">Subscribe</button>
    </form>
    <p class="newsletter-note">Get notified when new apps launch.</p>
  </div>
</div>
