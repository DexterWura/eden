<?php
  $days = $days ?? 60;
  $revenueByDay = $revenueByDay ?? [];
  $usersByDay = $usersByDay ?? [];
  $queues = [
    ['Pending startups', $pendingStartups ?? collect(), 'admin.startups.index', 'startups', 'fa-rocket', 'Review submissions waiting for approval.'],
    ['Hero requests', $heroRequests ?? collect(), 'admin.dashboard', 'hero', 'fa-star', 'Approve or decline homepage hero requests.'],
    ['Pending reports', $pendingReports ?? collect(), 'admin.startup-reports.index', 'reports', 'fa-flag', 'Resolve reports submitted by the community.'],
    ['Unread messages', $unreadMessages ?? collect(), 'admin.contact-messages.index', 'messages', 'fa-message', 'Open and reply to new contact messages.'],
    ['Failed website checks', $failedWebsiteChecks ?? collect(), 'admin.startup-websites.index', 'websites', 'fa-triangle-exclamation', 'Investigate repeatedly unreachable startup websites.'],
    ['Failed tasks', $failedTasks ?? collect(), 'admin.scheduled-tasks.index', 'tasks', 'fa-clock-rotate-left', 'Review failed scheduled operations.'],
    ['Pending ads & payments', $pendingAds ?? collect(), 'admin.ad-spots.index', 'ads', 'fa-rectangle-ad', 'Validate payment details and activate ad placements.'],
  ];
?>

<div class="dash-page-heading">
  <div>
    <h1 class="dash-page-title">Admin command center</h1>
    <p class="dash-page-description">Prioritized operational work across Eden.</p>
  </div>
  <span class="dash-status-indicator"><span aria-hidden="true"></span> Live queues</span>
</div>

<section aria-labelledby="attentionHeading">
  <div class="dash-section-heading">
    <h2 id="attentionHeading">Needs attention</h2>
    <p>Only queues you have permission to manage are shown.</p>
  </div>
  <div class="dash-operations-grid">
    <?php foreach ($queues as [$label, $items, $routeName, $type, $icon, $description]): ?>
      <?php if (
        ($type === 'startups' || $type === 'hero') && empty($canManageStartups)
        || $type === 'reports' && empty($canManageReports)
        || $type === 'messages' && empty($canManageMessages)
        || $type === 'websites' && empty($canManageWebsites)
        || $type === 'tasks' && empty($canManageTasks)
        || $type === 'ads' && empty($canManageAds)
      ) continue; ?>
      <a class="dash-operation-card<?= $items->isNotEmpty() ? ' dash-operation-card--attention' : '' ?>" href="<?= e(route($routeName)) ?>">
        <span class="dash-operation-icon"><i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i></span>
        <span class="dash-operation-copy">
          <strong><?= e($label) ?></strong>
          <small><?= e($description) ?></small>
        </span>
        <span class="dash-operation-count" aria-label="<?= e($items->count() . ' ' . strtolower($label)) ?>"><?= e($items->count()) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if (!empty($canManageStartups) && $heroRequests->isNotEmpty()): ?>
<section class="dash-card dash-card--priority" aria-labelledby="heroRequestsHeading">
  <div class="dash-card-header">
    <div>
      <h2 class="dash-card-title" id="heroRequestsHeading"><i class="fa-solid fa-star" aria-hidden="true"></i> Hero feature requests</h2>
      <p class="dash-card-subtitle">Confirm founder identity and LinkedIn details before approving.</p>
    </div>
    <span class="dash-badge dash-badge-warning"><?= e($heroRequests->count()) ?> pending</span>
  </div>
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr><th>Startup</th><th>Founder links</th><th>Requested</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
      <tbody>
      <?php foreach ($heroRequests as $request): ?>
        <?php $linkedinFounders = collect($request->founders_display)->filter(fn ($founder) => trim($founder['linkedin_url'] ?? '') !== ''); ?>
        <tr>
          <td><a href="<?= e(route('admin.startups.edit', $request)) ?>" class="dash-table-link"><?= e($request->name) ?></a></td>
          <td>
            <?php if ($linkedinFounders->isEmpty()): ?><span class="dash-muted">No LinkedIn profile</span><?php endif; ?>
            <?php foreach ($linkedinFounders as $founder): ?>
              <a href="<?= e($founder['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" class="dash-inline-link"><i class="fa-brands fa-linkedin" aria-hidden="true"></i> <?= e($founder['name']) ?></a>
            <?php endforeach; ?>
          </td>
          <td><?= e($request->updated_at->diffForHumans()) ?></td>
          <td class="dash-actions">
            <form action="<?= e(route('admin.hero-request.approve', $request)) ?>" method="post" data-confirm="Approve <?= e($request->name) ?> for the homepage hero?" data-confirm-label="Approve request">
              <?= csrf_field() ?>
              <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check" aria-hidden="true"></i> Approve</button>
            </form>
            <form action="<?= e(route('admin.hero-request.reject', $request)) ?>" method="post" data-confirm="Decline the hero request for <?= e($request->name) ?>?" data-confirm-label="Decline request">
              <?= csrf_field() ?>
              <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Decline</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>

<div class="dash-command-grid">
  <?php if (!empty($canManageStartups)): ?>
  <section class="dash-card" aria-labelledby="pendingStartupsHeading">
    <div class="dash-card-header"><h2 class="dash-card-title" id="pendingStartupsHeading">Pending startups</h2><a href="<?= e(route('admin.startups.index')) ?>">Open queue</a></div>
    <div class="dash-list">
      <?php foreach ($pendingStartups as $startup): ?>
        <a href="<?= e(route('admin.startups.edit', $startup)) ?>" class="dash-list-item"><span><strong><?= e($startup->name) ?></strong><small><?= e($startup->category ?: 'Uncategorized') ?> · submitted <?= e($startup->created_at->diffForHumans()) ?></small></span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <?php endforeach; ?>
      <?php if ($pendingStartups->isEmpty()): ?><p class="dash-empty-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><strong>Queue clear</strong><span>No startups are awaiting review.</span></p><?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($canManageMessages)): ?>
  <section class="dash-card" aria-labelledby="messagesHeading">
    <div class="dash-card-header"><h2 class="dash-card-title" id="messagesHeading">Unread messages</h2><a href="<?= e(route('admin.contact-messages.index')) ?>">View all</a></div>
    <div class="dash-list">
      <?php foreach ($unreadMessages as $message): ?>
        <a href="<?= e(route('admin.contact-messages.show', $message)) ?>" class="dash-list-item"><span><strong><?= e($message->subject ? ucfirst($message->subject) : $message->name) ?></strong><small><?= e(\Illuminate\Support\Str::limit($message->message, 88)) ?></small></span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <?php endforeach; ?>
      <?php if ($unreadMessages->isEmpty()): ?><p class="dash-empty-state"><i class="fa-solid fa-inbox" aria-hidden="true"></i><strong>Inbox clear</strong><span>No unread contact messages.</span></p><?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($canManageReports)): ?>
  <section class="dash-card" aria-labelledby="reportsHeading">
    <div class="dash-card-header"><h2 class="dash-card-title" id="reportsHeading">Pending reports</h2><a href="<?= e(route('admin.startup-reports.index')) ?>">Resolve reports</a></div>
    <div class="dash-list">
      <?php foreach ($pendingReports as $report): ?>
        <a href="<?= e(route('admin.startup-reports.index') . '#report-' . $report->id) ?>" class="dash-list-item"><span><strong><?= e($report->startup?->name ?? 'Deleted startup') ?></strong><small><?= e(\App\Models\StartupReport::reasonLabels()[$report->reason] ?? ucfirst($report->reason)) ?> · <?= e($report->created_at->diffForHumans()) ?></small></span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <?php endforeach; ?>
      <?php if ($pendingReports->isEmpty()): ?><p class="dash-empty-state"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><strong>No pending reports</strong><span>Community moderation is up to date.</span></p><?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($canManageWebsites) || !empty($canManageTasks)): ?>
  <section class="dash-card" aria-labelledby="failuresHeading">
    <div class="dash-card-header"><h2 class="dash-card-title" id="failuresHeading">Operational failures</h2></div>
    <div class="dash-list">
      <?php foreach ($failedWebsiteChecks as $startup): ?>
        <a href="<?= e(route('admin.startup-websites.index', ['filter' => 'with-website'])) ?>" class="dash-list-item"><span><strong><?= e($startup->name) ?></strong><small>Website failed <?= e($startup->website_consecutive_failures) ?> consecutive checks</small></span><i class="fa-solid fa-globe" aria-hidden="true"></i></a>
      <?php endforeach; ?>
      <?php foreach ($failedTasks as $task): ?>
        <a href="<?= e(route('admin.scheduled-tasks.index')) ?>" class="dash-list-item"><span><strong><?= e($task->display_name ?: $task->name) ?></strong><small><?= e(\Illuminate\Support\Str::limit($task->last_message ?: 'Task failed', 88)) ?></small></span><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></a>
      <?php endforeach; ?>
      <?php if ($failedWebsiteChecks->isEmpty() && $failedTasks->isEmpty()): ?><p class="dash-empty-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><strong>Systems healthy</strong><span>No failed checks or tasks.</span></p><?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($canManageAds)): ?>
  <section class="dash-card" aria-labelledby="adsHeading">
    <div class="dash-card-header"><h2 class="dash-card-title" id="adsHeading">Pending ads &amp; payments</h2><a href="<?= e(route('admin.ad-spots.index')) ?>">Open revenue queue</a></div>
    <div class="dash-list">
      <?php foreach ($pendingAds as $ad): ?>
        <a href="<?= e(route('admin.ad-spots.index') . '#ad-' . $ad->id) ?>" class="dash-list-item">
          <span><strong><?= e(ucwords(str_replace('-', ' ', $ad->placement))) ?></strong><small><?= e($ad->contact_email) ?> · <?= e($ad->payment_reference ? 'Payment ' . $ad->payment_reference : 'Payment reference missing') ?></small></span>
          <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
      <?php endforeach; ?>
      <?php if ($pendingAds->isEmpty()): ?><p class="dash-empty-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><strong>Revenue queue clear</strong><span>No ads are awaiting payment review or activation.</span></p><?php endif; ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<?php
  $platformMetrics = [];
  if (!empty($canManageStartups)) $platformMetrics = array_merge($platformMetrics, [['Total startups', $totalStartups], ['Active startups', $activeStartups], ['Launching today', $launchingToday]]);
  if (!empty($canManageUsers)) $platformMetrics[] = ['Users', $totalUsers];
  if (!empty($canManageSubscribers)) $platformMetrics[] = ['Subscribers', $totalSubscribers];
?>
<?php if ($platformMetrics): ?><div class="dash-kpi-row" aria-label="Platform overview">
  <?php foreach ($platformMetrics as [$label, $value]): ?>
  <div class="dash-kpi-card"><div class="dash-kpi-label"><?= e($label) ?></div><div class="dash-kpi-value"><?= e($value) ?></div></div>
  <?php endforeach; ?>
</div><?php endif; ?>

<?php if (!empty($canManagePayments) || !empty($canManageUsers)): ?>
<div class="dash-chart-grid">
  <?php if (!empty($canManagePayments)): ?><section class="dash-card"><div class="dash-card-header"><h2 class="dash-card-title">Money over time</h2><span class="dash-card-subtitle">Last <?= e($days) ?> days · cumulative</span></div><div class="dash-card-body"><div id="adminRevenueChart" class="analytics-chart" style="min-height:260px"></div></div></section><?php endif; ?>
  <?php if (!empty($canManageUsers)): ?><section class="dash-card"><div class="dash-card-header"><h2 class="dash-card-title">Users over time</h2><span class="dash-card-subtitle">New signups · last <?= e($days) ?> days</span></div><div class="dash-card-body"><div id="adminUsersChart" class="analytics-chart" style="min-height:260px"></div></div></section><?php endif; ?>
</div>
<?php endif; ?>

<script>
(function () {
  if (typeof ApexCharts === 'undefined') return;
  var styles = getComputedStyle(document.documentElement);
  var textColor = styles.getPropertyValue('--d-text-secondary').trim() || '#5f6368';
  var gridColor = styles.getPropertyValue('--d-border').trim() || '#e8eaed';
  var dates = [];
  for (var i = <?= (int) $days - 1 ?>; i >= 0; i -= 1) {
    var date = new Date();
    date.setDate(date.getDate() - i);
    dates.push(date.toISOString().slice(0, 10));
  }
  var revenueDaily = <?= json_encode($revenueByDay, JSON_THROW_ON_ERROR) ?>;
  var usersDaily = <?= json_encode($usersByDay, JSON_THROW_ON_ERROR) ?>;
  var total = 0;
  var revenue = dates.map(function (date) { total += parseFloat(revenueDaily[date] || 0); return total; });
  var users = dates.map(function (date) { return parseInt(usersDaily[date] || 0, 10); });
  var common = {
    chart: { height: 260, toolbar: { show: false }, fontFamily: 'Outfit, system-ui, sans-serif', background: 'transparent' },
    grid: { borderColor: gridColor, strokeDashArray: 3 },
    dataLabels: { enabled: false },
    xaxis: { categories: dates.map(function (date) { return date.slice(5); }), labels: { style: { colors: textColor } } },
    yaxis: { labels: { style: { colors: textColor } } },
    tooltip: { theme: 'light' },
    stroke: { curve: 'smooth', width: 2 }
  };
  var revenueChart = document.getElementById('adminRevenueChart');
  var usersChart = document.getElementById('adminUsersChart');
  if (revenueChart) new ApexCharts(revenueChart, Object.assign({}, common, { chart: Object.assign({}, common.chart, { type: 'area' }), series: [{ name: 'Revenue', data: revenue }], colors: ['#1a73e8'], fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.03 } } })).render();
  if (usersChart) new ApexCharts(usersChart, Object.assign({}, common, { chart: Object.assign({}, common.chart, { type: 'line' }), series: [{ name: 'New users', data: users }], colors: ['#7c3aed'] })).render();
})();
</script>
