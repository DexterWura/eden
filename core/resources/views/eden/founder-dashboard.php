<?php
  $myStartups = $myStartups ?? collect();
  $startupProfiles = $startupProfiles ?? collect();
  $totals = $totals ?? [];
  $activity = $activity ?? collect();
  $savedStartups = $savedStartups ?? ['count' => 0, 'recent' => collect()];
  $unreadNotifications = $unreadNotifications ?? collect();
?>

<header class="founder-home-header">
  <div>
    <p class="founder-home-eyebrow">Founder workspace</p>
    <h1 class="dash-page-title">Welcome, <?= e(auth()->user()->name ?? 'founder') ?></h1>
    <p class="founder-home-lede">Track your startup presence, community momentum, and next best actions.</p>
  </div>
  <a href="<?= e(route('founder.startups.create')) ?>" class="dash-btn dash-btn-primary founder-home-primary-action">
    <i class="fa-solid fa-plus" aria-hidden="true"></i> Add startup
  </a>
</header>

<?php include __DIR__ . '/founder/dashboard/_notifications.php'; ?>

<?php if ($myStartups->isEmpty()): ?>
  <?php include __DIR__ . '/founder/dashboard/_onboarding.php'; ?>
<?php else: ?>
  <?php include __DIR__ . '/founder/dashboard/_metrics.php'; ?>

  <div class="founder-dashboard-grid">
    <section class="founder-dashboard-main" aria-label="Startup performance">
      <?php include __DIR__ . '/founder/dashboard/_startup-profiles.php'; ?>
      <?php include __DIR__ . '/founder/dashboard/_activity.php'; ?>
    </section>
    <aside class="founder-dashboard-rail" aria-label="Founder shortcuts">
      <?php include __DIR__ . '/founder/dashboard/_saved-startups.php'; ?>
      <?php include __DIR__ . '/founder/dashboard/_quick-links.php'; ?>
    </aside>
  </div>
<?php endif; ?>

<script>
document.querySelectorAll('[data-copy-text]').forEach(function (button) {
  button.addEventListener('click', function () {
    navigator.clipboard.writeText(button.getAttribute('data-copy-text') || '').then(function () {
      var original = button.innerHTML;
      button.textContent = 'Copied';
      setTimeout(function () { button.innerHTML = original; }, 1500);
    });
  });
});
</script>

<?php if (auth()->check() && ! auth()->user()->isPro()): ?>
<script>
(function() {
  setTimeout(function() {
    if (typeof edenPromoToast === 'function') {
      edenPromoToast({ key: 'pro_dashboard', message: 'Unlock analytics, hero featuring, and more — go Pro.', ctaText: 'View plans', ctaHref: typeof edenPricingUrl !== 'undefined' ? edenPricingUrl : '<?= e(url('/pricing')) ?>' });
    }
  }, 1500);
})();
</script>
<?php endif; ?>
