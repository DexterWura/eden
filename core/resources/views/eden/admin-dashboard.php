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
<?php $heroRequests = $heroRequests ?? collect(); ?>
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
