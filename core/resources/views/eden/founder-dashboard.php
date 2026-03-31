<h1 class="dash-page-title">Home</h1>
<?php $unreadNotifications = $unreadNotifications ?? collect(); ?>
<?php if ($unreadNotifications->isNotEmpty()): ?>
<?php foreach ($unreadNotifications as $notif):
  $data = is_array($notif->data) ? $notif->data : json_decode($notif->data, true);
  $title = $data['title'] ?? 'Notice';
  $message = $data['message'] ?? '';
?>
<div style="background:var(--surface-hover,#1a1d28);border:1px solid var(--border,#2a2e3d);border-left:4px solid #f59e0b;border-radius:8px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:flex-start;gap:12px">
  <i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;margin-top:2px"></i>
  <div style="flex:1">
    <strong style="display:block;margin-bottom:4px"><?= e($title) ?></strong>
    <span style="color:var(--text-muted,#8b90a0);font-size:0.92rem"><?= e($message) ?></span>
  </div>
  <form action="<?= e(route('founder.notifications.dismiss', $notif->id)) ?>" method="post" style="flex:none">
    <?= csrf_field() ?>
    <button type="submit" class="dash-btn dash-btn-secondary" style="padding:4px 12px;font-size:0.8rem">Dismiss</button>
  </form>
</div>
<?php endforeach; ?>
<?php endif; ?>
<div class="dash-welcome">
  <strong>Welcome back!</strong> Here's an overview of your startups and key metrics.
</div>
<?php
  $myStartups = $myStartups ?? [];
  $totalUpvotes = $totalUpvotes ?? 0;
  $totalViews = $totalViews ?? 0;
  $totalClicks = $totalClicks ?? 0;
  $totalComments = $totalComments ?? 0;
  $totalRevenue = $totalRevenue ?? 0;
  $totalMrr = $totalMrr ?? 0;
?>
<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total upvotes</div>
    <div class="dash-kpi-value"><?= e($totalUpvotes) ?></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Your startups</div>
    <div class="dash-kpi-value"><?= e(count($myStartups)) ?></div>
  </div>
  <?php if (auth()->user()->isPro()): ?>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total views</div>
    <div class="dash-kpi-value"><?= number_format($totalViews) ?></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">MRR</div>
    <div class="dash-kpi-value">$<?= number_format($totalMrr, 2) ?></div>
  </div>
  <?php endif; ?>
</div>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Your startups</span>
    <a href="<?= e(url('/founder/startups')) ?>" class="dash-table-link">View all</a>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Startup</th>
            <th>Category</th>
            <th>Upvotes</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($myStartups)): ?>
          <tr>
            <td colspan="4" class="dash-placeholder">No startups yet. <a href="<?= e(url('/founder/startups/create')) ?>">Add your first startup</a>.</td>
          </tr>
          <?php else: ?>
          <?php foreach (is_array($myStartups) ? array_slice($myStartups, 0, 5) : $myStartups->take(5) as $s): ?>
          <tr>
            <td><a href="<?= e(url('/startup/' . $s->slug)) ?>" class="dash-table-link" target="_blank"><?= e($s->name) ?></a></td>
            <td><?= e($s->category ?? '—') ?></td>
            <td><?= e($s->upvotes ?? 0) ?></td>
            <td>
              <?php if ($s->status === 'pending'): ?>
                <span style="display:inline-block;padding:2px 8px;font-size:0.75rem;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:600">Pending review</span>
              <?php else: ?>
                <?= e($s->status ?? 'active') ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="dash-card-footer">
    <a href="<?= e(url('/founder/startups')) ?>">My startups →</a>
  </div>
</div>
<?php $isPro = auth()->user()->isPro(); ?>
<?php if ($isPro): ?>
<?php
  $heroEligibleStartups = collect($myStartups)->filter(function ($s) {
      return $s->isActive() && $s->hasFounderWithLinkedin() && !$s->featured_on_hero;
  });
?>
<?php if ($heroEligibleStartups->isNotEmpty() || collect($myStartups)->contains(fn ($s) => $s->hero_request_status)): ?>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title"><i class="fa-solid fa-star" style="color:#f59e0b;margin-right:4px"></i> Featured on hero</span>
  </div>
  <div class="dash-card-body">
    <p style="color:var(--text-muted,#8b90a0);font-size:0.92rem;margin-bottom:14px">Request your startup to be featured on the homepage hero section. Founders must have a LinkedIn link set.</p>
    <?php foreach ($myStartups as $s): ?>
      <?php if ($s->featured_on_hero): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border,#2a2e3d)">
          <span style="flex:1;font-weight:500"><?= e($s->name) ?></span>
          <span style="display:inline-block;padding:3px 10px;font-size:0.78rem;border-radius:4px;background:#d1fae5;color:#065f46;font-weight:600"><i class="fa-solid fa-check"></i> Featured</span>
        </div>
      <?php elseif ($s->hero_request_status === 'pending'): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border,#2a2e3d)">
          <span style="flex:1;font-weight:500"><?= e($s->name) ?></span>
          <span style="display:inline-block;padding:3px 10px;font-size:0.78rem;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:600"><i class="fa-solid fa-clock"></i> Pending approval</span>
        </div>
      <?php elseif ($s->hero_request_status === 'rejected'): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border,#2a2e3d)">
          <span style="flex:1;font-weight:500"><?= e($s->name) ?></span>
          <span style="display:inline-block;padding:3px 10px;font-size:0.78rem;border-radius:4px;background:#fee2e2;color:#991b1b;font-weight:600">Request declined</span>
          <?php if ($s->isActive() && $s->hasFounderWithLinkedin()): ?>
          <form action="<?= e(route('founder.hero-request', $s->id)) ?>" method="post" style="flex:none">
            <?= csrf_field() ?>
            <button type="submit" class="dash-btn dash-btn-secondary" style="padding:4px 12px;font-size:0.8rem">Request again</button>
          </form>
          <?php endif; ?>
        </div>
      <?php elseif ($s->isActive() && $s->hasFounderWithLinkedin()): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border,#2a2e3d)">
          <span style="flex:1;font-weight:500"><?= e($s->name) ?></span>
          <form action="<?= e(route('founder.hero-request', $s->id)) ?>" method="post" style="flex:none">
            <?= csrf_field() ?>
            <button type="submit" class="dash-btn dash-btn-primary" style="padding:5px 14px;font-size:0.85rem"><i class="fa-solid fa-star"></i> Request to be featured</button>
          </form>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
<?php else: ?>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title"><i class="fa-solid fa-star" style="color:#f59e0b;margin-right:4px"></i> Featured on hero</span>
  </div>
  <div class="dash-card-body" style="text-align:center;padding:28px 20px">
    <p style="color:var(--text-muted,#8b90a0);font-size:0.92rem;margin-bottom:14px">Upgrade to <strong>Pro</strong> to request your startup to be featured on the homepage hero section.</p>
    <a href="<?= e(url('/pricing')) ?>" class="dash-btn dash-btn-primary" style="text-decoration:none"><i class="fa-solid fa-crown"></i> Upgrade to Pro — $9.99</a>
  </div>
</div>
<?php endif; ?>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Quick links</span>
  </div>
  <div class="dash-card-body" style="display: flex; flex-wrap: wrap; gap: 12px;">
    <a href="<?= e(url('/founder/startups/create')) ?>" class="dash-btn dash-btn-primary" style="text-decoration: none;"><i class="fa-solid fa-plus"></i> Add startup</a>
    <a href="<?= e(url('/founder/startups')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-building-user"></i> My startups</a>
    <a href="<?= e(url('/founder/badges')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-certificate"></i> Badges</a>
    <a href="<?= e(url('/founder/upvotes')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-arrow-up"></i> Upvotes</a>
    <?php if (auth()->user()->isPro()): ?><a href="<?= e(url('/founder/analytics')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-chart-line"></i> Analytics</a><?php endif; ?>
    <a href="<?= e(url('/founder/settings')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>
<?php if (auth()->check() && !auth()->user()->isPro()): ?>
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
