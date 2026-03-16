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
<?php
  $heroRequests = $heroRequests ?? collect();
  $days = $days ?? 60;
  $revenueByDay = $revenueByDay ?? [];
  $usersByDay = $usersByDay ?? [];
?>
<?php if ($heroRequests->isNotEmpty()): ?>
<div class="dash-card" style="border-left:4px solid #f59e0b">
  <div class="dash-card-header">
    <span class="dash-card-title"><i class="fa-solid fa-star" style="color:#f59e0b;margin-right:4px"></i> Hero feature requests <span style="background:#fef3c7;color:#92400e;font-size:0.75rem;padding:2px 8px;border-radius:10px;margin-left:6px;font-weight:600"><?= $heroRequests->count() ?></span></span>
  </div>
  <div class="dash-card-body" style="padding:0">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Startup</th>
            <th>Founders with LinkedIn</th>
            <th>Requested</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($heroRequests as $hr):
            $linkedinFounders = collect($hr->founders_display)->filter(fn ($f) => trim($f['linkedin_url'] ?? '') !== '');
          ?>
          <tr>
            <td><a href="<?= e(url('/startup/' . $hr->slug)) ?>" class="dash-table-link" target="_blank"><?= e($hr->name) ?></a></td>
            <td>
              <?php foreach ($linkedinFounders as $lf): ?>
                <a href="<?= e($lf['linkedin_url']) ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:4px;margin-right:8px;font-size:0.85rem;color:var(--accent,#00d4aa);text-decoration:none">
                  <i class="fa-brands fa-linkedin" style="color:#0a66c2"></i> <?= e($lf['name']) ?>
                </a>
              <?php endforeach; ?>
            </td>
            <td style="white-space:nowrap;font-size:0.85rem;color:var(--text-muted,#8b90a0)"><?= e($hr->updated_at->diffForHumans()) ?></td>
            <td style="text-align:right;white-space:nowrap">
              <form action="<?= e(route('admin.hero-request.approve', $hr->id)) ?>" method="post" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="dash-btn dash-btn-primary" style="padding:4px 12px;font-size:0.8rem"><i class="fa-solid fa-check"></i> Approve</button>
              </form>
              <form action="<?= e(route('admin.hero-request.reject', $hr->id)) ?>" method="post" style="display:inline;margin-left:4px">
                <?= csrf_field() ?>
                <button type="submit" class="dash-btn dash-btn-secondary" style="padding:4px 12px;font-size:0.8rem"><i class="fa-solid fa-xmark"></i> Decline</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
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
    <span class="dash-card-title"><i class="fa-solid fa-wave-square"></i> Money over time</span>
    <span class="dash-card-subtitle">Last <?= e($days) ?> days · cumulative</span>
  </div>
  <div class="dash-card-body">
    <div id="adminRevenueChart" class="analytics-chart" style="min-height:260px;"></div>
  </div>
</div>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title"><i class="fa-solid fa-user-group"></i> Users over time</span>
    <span class="dash-card-subtitle">New signups per day · last <?= e($days) ?> days</span>
  </div>
  <div class="dash-card-body">
    <div id="adminUsersChart" class="analytics-chart" style="min-height:240px;"></div>
  </div>
</div>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Recent contact messages</span>
    <a href="<?= e(url('/backoffice/contact-messages')) ?>" class="dash-table-link">View all</a>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          <?php $recentContactMessages = $recentContactMessages ?? []; ?>
          <?php if (count($recentContactMessages) === 0): ?>
          <tr>
            <td colspan="5" class="dash-placeholder">No contact messages yet.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($recentContactMessages as $m): ?>
          <tr>
            <td style="white-space: nowrap;"><?= e($m->created_at->format('M j, Y H:i')) ?></td>
            <td><?= e($m->name) ?></td>
            <td><a href="mailto:<?= e($m->email) ?>"><?= e($m->email) ?></a></td>
            <td><?= e($m->subject ? ucfirst($m->subject) : '—') ?></td>
            <td style="max-width: 200px;"><?= e(mb_strlen($m->message) > 60 ? mb_substr($m->message, 0, 60) . '…' : $m->message) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="dash-card-footer">
    <a href="<?= e(url('/backoffice/contact-messages')) ?>">View all contact messages →</a>
  </div>
</div>
<script>
(function() {
  if (typeof ApexCharts === 'undefined') return;

  var isDark = document.documentElement.classList.contains('dashboard')
    ? true
    : (document.documentElement.getAttribute('data-theme') !== 'light');
  var textColor = isDark ? '#8b90a0' : '#5f6368';
  var gridColor = isDark ? '#2a2e3d' : '#e8eaed';
  var accentColor = '#00d4aa';

  function dateRange(days) {
    var arr = [];
    for (var i = days - 1; i >= 0; i--) {
      var d = new Date();
      d.setDate(d.getDate() - i);
      arr.push(d.toISOString().slice(0, 10));
    }
    return arr;
  }

  function cumulativeFromDaily(dates, dailyData) {
    var cum = 0;
    return dates.map(function(d) {
      cum += (parseFloat(dailyData[d]) || 0);
      return cum;
    });
  }

  var days = <?= (int) $days ?>;
  var revenueDaily = <?= json_encode($revenueByDay, JSON_THROW_ON_ERROR) ?>;
  var usersDaily = <?= json_encode($usersByDay, JSON_THROW_ON_ERROR) ?>;
  var dates = dateRange(days);

  var revenueSeries = cumulativeFromDaily(dates, revenueDaily);
  var usersSeries = dates.map(function(d) { return parseInt(usersDaily[d] || 0, 10); });

  var commonOptions = {
    chart: { fontFamily: 'Outfit, system-ui, sans-serif', background: 'transparent' },
    grid: { borderColor: gridColor, strokeDashArray: 3 },
    xaxis: { labels: { style: { colors: textColor } } },
    yaxis: { labels: { style: { colors: textColor } } },
    tooltip: { theme: isDark ? 'dark' : 'light' }
  };

  if (document.getElementById('adminRevenueChart')) {
    var revChart = new ApexCharts(document.getElementById('adminRevenueChart'), {
      series: [{ name: 'Total revenue (all startups)', data: revenueSeries }],
      chart: {
        type: 'area',
        height: 260,
        toolbar: { show: true, tools: { zoom: true, pan: true, reset: true } },
        zoom: { enabled: true },
        animations: { enabled: true, easing: 'easeInOutQuart', speed: 600 }
      },
      stroke: { curve: 'smooth', width: 2 },
      fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
      dataLabels: { enabled: false },
      colors: [accentColor],
      xaxis: { categories: dates.map(function(d) { return d.slice(5); }) },
      ...commonOptions
    });
    revChart.render();
  }

  if (document.getElementById('adminUsersChart')) {
    var userChart = new ApexCharts(document.getElementById('adminUsersChart'), {
      series: [{ name: 'New users', data: usersSeries }],
      chart: {
        type: 'line',
        height: 240,
        toolbar: { show: true, tools: { zoom: true, pan: true, reset: true } },
        zoom: { enabled: true },
        animations: { enabled: true, easing: 'easeInOutQuart', speed: 600 }
      },
      stroke: { curve: 'smooth', width: 2 },
      markers: { size: 0 },
      dataLabels: { enabled: false },
      colors: ['#6366f1'],
      xaxis: { categories: dates.map(function(d) { return d.slice(5); }) },
      ...commonOptions
    });
    userChart.render();
  }
})();
</script>
<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Quick links</span>
  </div>
  <div class="dash-card-body" style="display: flex; flex-wrap: wrap; gap: 12px;">
    <a href="<?= e(url('/backoffice/startups/create')) ?>" class="dash-btn dash-btn-primary" style="text-decoration: none;"><i class="fa-solid fa-plus"></i> Add startup</a>
    <a href="<?= e(url('/backoffice/contact-messages')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-message"></i> Contact messages</a>
    <a href="<?= e(url('/backoffice/users')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-user"></i> Users</a>
    <a href="<?= e(url('/backoffice/subscribers')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-envelope"></i> Subscribers</a>
    <a href="<?= e(url('/backoffice/reports')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-chart-line"></i> Reports</a>
    <a href="<?= e(url('/backoffice/settings')) ?>" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>
