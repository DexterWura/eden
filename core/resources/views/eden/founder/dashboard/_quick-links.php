<section class="dash-card founder-rail-card" aria-labelledby="founder-actions-title">
  <div class="dash-card-header">
    <h2 id="founder-actions-title" class="dash-card-title">Founder tools</h2>
  </div>
  <nav class="founder-tool-list" aria-label="Founder tools">
    <a href="<?= e(route('founder.badges')) ?>"><i class="fa-solid fa-certificate" aria-hidden="true"></i><span><strong>Badges</strong><small>Share your Eden presence</small></span></a>
    <a href="<?= e(route('founder.revenue-api')) ?>"><i class="fa-solid fa-code" aria-hidden="true"></i><span><strong>Revenue API</strong><small>Connect revenue activity</small></span></a>
    <?php if (auth()->user()->isPro()): ?>
      <a href="<?= e(route('founder.analytics')) ?>"><i class="fa-solid fa-chart-line" aria-hidden="true"></i><span><strong>Analytics</strong><small>Review performance trends</small></span></a>
      <a href="<?= e(route('founder.fundraising.index')) ?>"><i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i><span><strong>Fund raising</strong><small>Manage investor visibility</small></span></a>
    <?php else: ?>
      <a href="<?= e(url('/pricing')) ?>"><i class="fa-solid fa-crown" aria-hidden="true"></i><span><strong>Unlock Pro tools</strong><small>Analytics, fundraising, and more</small></span></a>
    <?php endif; ?>
    <a href="<?= e(route('founder.settings')) ?>"><i class="fa-solid fa-gear" aria-hidden="true"></i><span><strong>Settings</strong><small>Manage your founder account</small></span></a>
  </nav>
</section>

<?php
  $heroCandidates = $myStartups->filter(
    fn ($startup) => $startup->isActive() && $startup->hasFounderWithLinkedin() && ! $startup->featured_on_hero
  );
?>
<?php if (auth()->user()->isPro() && ($heroCandidates->isNotEmpty() || $myStartups->contains(fn ($startup) => $startup->featured_on_hero))): ?>
<section class="dash-card founder-rail-card" aria-labelledby="founder-hero-title">
  <div class="dash-card-header">
    <div>
      <h2 id="founder-hero-title" class="dash-card-title">Homepage feature</h2>
      <p class="dash-card-subtitle">Highlight an eligible app.</p>
    </div>
  </div>
  <div class="founder-hero-list">
    <?php foreach ($myStartups as $startup): ?>
      <?php if ($startup->featured_on_hero): ?>
        <p><strong><?= e($startup->name) ?></strong><span class="dash-badge dash-badge-success">Featured</span></p>
      <?php elseif ($startup->hero_request_status === 'pending'): ?>
        <p><strong><?= e($startup->name) ?></strong><span class="dash-badge dash-badge-pending">Pending</span></p>
      <?php elseif ($startup->isActive() && $startup->hasFounderWithLinkedin()): ?>
        <form action="<?= e(route('founder.hero-request', $startup)) ?>" method="post">
          <?= csrf_field() ?>
          <span><?= e($startup->name) ?></span>
          <button type="submit" class="dash-btn dash-btn-secondary">Request</button>
        </form>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
