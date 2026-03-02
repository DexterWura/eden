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
  <strong>Welcome back!</strong> Here's an overview of your startups and upvotes.
</div>
<?php $myStartups = $myStartups ?? []; $totalUpvotes = $totalUpvotes ?? 0; ?>
<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total upvotes</div>
    <div class="dash-kpi-value"><?= e($totalUpvotes) ?></div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Your startups</div>
    <div class="dash-kpi-value"><?= e(count($myStartups)) ?></div>
  </div>
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
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Quick links</span>
  </div>
  <div class="dash-card-body" style="display: flex; flex-wrap: wrap; gap: 12px;">
    <a href="<?= e(url('/founder/startups/create')) ?>" class="dash-btn dash-btn-primary" style="text-decoration: none;"><i class="fa-solid fa-plus"></i> Add startup</a>
    <a href="<?= e(url('/founder/startups')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-building-user"></i> My startups</a>
    <a href="<?= e(url('/founder/badges')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-certificate"></i> Badges</a>
    <a href="<?= e(url('/founder/upvotes')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-arrow-up"></i> Upvotes</a>
    <a href="<?= e(url('/founder/settings')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>
