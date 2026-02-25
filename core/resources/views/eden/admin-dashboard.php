<h1 class="dash-page-title">Home</h1>
<div class="dash-welcome">
  <strong>Welcome back!</strong> Here’s a quick overview of your Eden admin.
</div>
<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total startups</div>
    <div class="dash-kpi-value"><?= e($totalStartups ?? 0) ?></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Active startups</div>
    <div class="dash-kpi-value"><?= e($activeStartups ?? 0) ?></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Launching today</div>
    <div class="dash-kpi-value"><?= e($launchingToday ?? 0) ?></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Users</div>
    <div class="dash-kpi-value"><?= e($totalUsers ?? 0) ?></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Subscribers</div>
    <div class="dash-kpi-value"><?= e($totalSubscribers ?? 0) ?></div>
  </div>
</div>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Recent startups</span>
    <a href="<?= e(url('/backoffice/startups')) ?>" class="dash-table-link">View all</a>
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
          <?php $recentStartups = $recentStartups ?? []; ?>
          <?php if (count($recentStartups) === 0): ?>
          <tr>
            <td colspan="4" class="dash-placeholder">No startups yet.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($recentStartups as $s): ?>
          <tr>
            <td><a href="<?= e(url('/startup/' . $s->slug)) ?>" class="dash-table-link" target="_blank"><?= e($s->name) ?></a></td>
            <td><?= e($s->category ?? '—') ?></td>
            <td><?= e($s->upvotes ?? 0) ?></td>
            <td><?= e($s->status ?? 'active') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="dash-card-footer">
    <a href="<?= e(url('/backoffice/startups')) ?>">View all startups →</a>
  </div>
</div>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Quick links</span>
  </div>
  <div class="dash-card-body" style="display: flex; flex-wrap: wrap; gap: 12px;">
    <a href="<?= e(url('/backoffice/startups/create')) ?>" class="dash-btn dash-btn-primary" style="text-decoration: none;"><i class="fa-solid fa-plus"></i> Add startup</a>
    <a href="<?= e(url('/backoffice/users')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-user"></i> Users</a>
    <a href="<?= e(url('/backoffice/subscribers')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-envelope"></i> Subscribers</a>
    <a href="<?= e(url('/backoffice/reports')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-chart-line"></i> Reports</a>
    <a href="<?= e(url('/backoffice/settings')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>
