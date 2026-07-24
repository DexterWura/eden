<h1 class="dash-page-title">Apps</h1>
<div class="dash-welcome">
  Manage apps: view founders, disable, ban, activate, set featured, and edit details.
</div>

<div class="dash-kpi-row">
  <div class="dash-kpi-card" style="{{ ($countPending ?? 0) > 0 ? 'border-left:3px solid #f59e0b' : '' }}">
    <div class="dash-kpi-label">Pending review</div>
    <div class="dash-kpi-value">{{ $countPending ?? 0 }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Active</div>
    <div class="dash-kpi-value">{{ $countActive }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Disabled</div>
    <div class="dash-kpi-value">{{ $countDisabled }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Banned</div>
    <div class="dash-kpi-value">{{ $countBanned }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Dormant</div>
    <div class="dash-kpi-value">{{ $countDormant ?? 0 }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Featured</div>
    <div class="dash-kpi-value">{{ $countFeatured }}</div>
  </div>
  <div class="dash-kpi-card" style="{{ ($countNeedsEnrichment ?? 0) > 0 ? 'border-left:3px solid #8b5cf6' : '' }}">
    <div class="dash-kpi-label">Needs enrichment</div>
    <div class="dash-kpi-value">{{ $countNeedsEnrichment ?? 0 }}</div>
  </div>
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">All apps</span>
    <a href="{{ route('admin.startups.create') }}" class="dash-btn dash-btn-primary" style="margin-left: auto;">
      <i class="fa-solid fa-plus"></i> Add app
    </a>
  </div>
  <div class="dash-card-body">
    <form method="get" action="{{ route('admin.startups.index') }}" class="dash-startups-filters" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
      <input type="text" name="q" value="{{ $search }}" placeholder="Search name, founder, category…" class="dash-search" style="max-width: 260px;">
      <select name="status" class="dash-search" style="max-width: 160px;">
        <option value="">All statuses</option>
        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
        <option value="disabled" {{ $statusFilter === 'disabled' ? 'selected' : '' }}>Disabled</option>
        <option value="banned" {{ $statusFilter === 'banned' ? 'selected' : '' }}>Banned</option>
        <option value="dormant" {{ $statusFilter === 'dormant' ? 'selected' : '' }}>Dormant</option>
      </select>
      <select name="quality" class="dash-search" style="max-width: 180px;">
        <option value="">All content quality</option>
        <option value="needs-enrichment" {{ ($qualityFilter ?? '') === 'needs-enrichment' ? 'selected' : '' }}>Needs enrichment</option>
        <option value="reviewed" {{ ($qualityFilter ?? '') === 'reviewed' ? 'selected' : '' }}>Editorially reviewed</option>
      </select>
      <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
    </form>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>App</th>
            <th>Founder</th>
            <th>Category</th>
            <th>Status</th>
            <th>Profile</th>
            <th>Featured</th>
            <th>Upvotes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($startups as $startup)
          <tr data-startup-id="{{ $startup->id }}">
            <td>
              <a href="{{ url('/startup/' . $startup->slug) }}" target="_blank" class="dash-table-link">{{ $startup->name }}</a>
              @if($startup->tagline)
                <div style="font-size: 0.8rem; color: var(--d-text-secondary); margin-top: 2px;">{{ Str::limit($startup->tagline, 50) }}</div>
              @endif
            </td>
            <td>
              {{ $startup->founder_name ?? '—' }}
              @if($startup->founder_email)
                <div style="font-size: 0.8rem; color: var(--d-text-secondary);">{{ $startup->founder_email }}</div>
              @endif
            </td>
            <td>{{ $startup->category ?? '—' }}</td>
            <td>
              @if($startup->status === 'pending')
                <span class="dash-badge dash-badge-pending">Pending</span>
              @elseif($startup->status === 'active')
                <span class="dash-badge dash-badge-success">Active</span>
              @elseif($startup->status === 'disabled')
                <span class="dash-badge dash-badge-warning">Disabled</span>
              @elseif($startup->status === 'dormant')
                <span class="dash-badge dash-badge-info">Dormant</span>
              @else
                <span class="dash-badge dash-badge-danger">Banned</span>
              @endif
            </td>
            <td>
              <strong>{{ $startup->content_completeness_score }}%</strong>
              @if($startup->editorial_reviewed_at)
                <div style="font-size:.75rem;color:#059669">Reviewed</div>
              @elseif(!$startup->hasSubstantiveContent())
                <div style="font-size:.75rem;color:#8b5cf6">Enrich</div>
              @endif
            </td>
            <td>{{ $startup->is_featured ? 'Yes' : '—' }}</td>
            <td>{{ $startup->upvotes }}</td>
            <td>
              <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                <a href="{{ route('admin.startups.edit', $startup) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;"><i class="fa-solid fa-pen"></i> Edit</a>
                @if($startup->isPending())
                  <button type="button" class="dash-btn startup-action" style="padding: 4px 10px; font-size: 0.8rem; background: #059669; color: #fff; border: none;" data-action="activate" data-url="{{ route('admin.startups.activate', $startup) }}"><i class="fa-solid fa-check"></i> Approve</button>
                @elseif($startup->isActive())
                  <button type="button" class="dash-btn dash-btn-secondary startup-action" style="padding: 4px 10px; font-size: 0.8rem;" data-action="disable" data-url="{{ route('admin.startups.disable', $startup) }}">Disable</button>
                @else
                  <button type="button" class="dash-btn dash-btn-primary startup-action" style="padding: 4px 10px; font-size: 0.8rem;" data-action="activate" data-url="{{ route('admin.startups.activate', $startup) }}">Set active</button>
                @endif
                @if($startup->isBanned())
                  <button type="button" class="dash-btn dash-btn-primary startup-action" style="padding: 4px 10px; font-size: 0.8rem;" data-action="unban" data-url="{{ route('admin.startups.unban', $startup) }}">Unban</button>
                @else
                  <button type="button" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #dc2626; color: #fff; border: none;" data-action="ban" data-url="{{ route('admin.startups.ban', $startup) }}">Ban</button>
                @endif
                <button type="button" class="dash-btn dash-btn-secondary startup-action startup-featured" style="padding: 4px 10px; font-size: 0.8rem;" data-url="{{ route('admin.startups.toggle-featured', $startup) }}" data-featured="{{ $startup->is_featured ? '1' : '0' }}">
                  {{ $startup->is_featured ? 'Unfeature' : 'Feature' }}
                </button>
                <button type="button" class="dash-btn dash-btn-secondary startup-add-upvotes" style="padding: 4px 10px; font-size: 0.8rem;" data-url="{{ route('admin.startups.add-upvotes', $startup) }}" data-startup="{{ e($startup->name) }}">
                  <i class="fa-solid fa-arrow-up"></i> Add upvotes
                </button>
                @if($startup->hasLinkedInFounders ?? false)
                  <form action="{{ route('admin.startups.toggle-hero', $startup) }}" method="post" style="display: inline;">
                    @csrf
                    @if($startup->featured_on_hero)
                      <button type="submit" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #7c3aed; color: #fff; border: none;">Unfeature from hero</button>
                    @else
                      <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Feature on hero</button>
                    @endif
                  </form>
                @endif
                @if($startup->status === 'disabled')
                  <button type="button" class="dash-btn startup-delete" style="padding: 4px 10px; font-size: 0.8rem; background: #991b1b; color: #fff; border: none;" data-url="{{ route('admin.startups.destroy', $startup) }}" data-name="{{ e($startup->name) }}">
                    <i class="fa-solid fa-trash"></i> Delete
                  </button>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="dash-placeholder">No apps found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($startups->hasPages())
      <div class="dash-card-footer">
        {{ $startups->links() }}
      </div>
    @endif
  </div>
</div>

<div id="deleteStartupDialog" class="dash-dialog" role="dialog" aria-modal="true" aria-labelledby="deleteStartupDialogTitle" aria-describedby="deleteStartupDialogMessage" hidden>
  <div class="dash-dialog-backdrop"></div>
  <div class="dash-dialog-box">
    <div class="dash-dialog-header">
      <h2 id="deleteStartupDialogTitle" class="dash-dialog-title">Delete app</h2>
    </div>
    <div class="dash-dialog-content">
      <p class="dash-dialog-body" id="deleteStartupDialogMessage">Are you sure you want to delete this app? This cannot be undone.</p>
    </div>
    <div class="dash-dialog-actions">
      <button type="button" class="dash-btn dash-btn-secondary" id="deleteStartupDialogCancel" data-dialog-initial-focus>Cancel</button>
      <button type="button" class="dash-btn dash-btn-danger" id="deleteStartupDialogConfirm">Delete</button>
    </div>
  </div>
</div>

<div id="upvoteDialog" class="dash-dialog" role="dialog" aria-modal="true" aria-labelledby="upvoteDialogTitle" aria-describedby="upvoteDialogMessage" hidden>
  <div class="dash-dialog-backdrop"></div>
  <div class="dash-dialog-box">
    <div class="dash-dialog-header"><h2 id="upvoteDialogTitle" class="dash-dialog-title">Add legitimate upvotes</h2></div>
    <div class="dash-dialog-content">
      <p id="upvoteDialogMessage">Enter the number of verified upvotes to add.</p>
      <label class="dash-label" for="upvoteCount">Upvotes (1–500)</label>
      <input class="dash-input" id="upvoteCount" type="number" min="1" max="500" step="1" inputmode="numeric" required data-dialog-initial-focus>
      <p class="dash-field-error" id="upvoteError" role="alert" hidden>Enter a whole number between 1 and 500.</p>
    </div>
    <div class="dash-dialog-actions">
      <button type="button" class="dash-btn dash-btn-secondary" id="upvoteDialogCancel">Cancel</button>
      <button type="button" class="dash-btn dash-btn-primary" id="upvoteDialogConfirm">Add upvotes</button>
    </div>
  </div>
</div>

<script>
(function() {
  var token = document.querySelector('meta[name="csrf-token"]');
  var csrf = token ? token.getAttribute('content') : '';
  document.querySelectorAll('.startup-action').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var url = this.getAttribute('data-url');
      var row = this.closest('tr');
      var self = this;
      if (!url) return;
      self.disabled = true;
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _token: csrf })
      }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.status === 'success') {
          if (typeof notify === 'function') notify('success', data.message);
          window.location.reload();
        } else {
          if (typeof notify === 'function') notify('error', data.message || 'Action failed');
          self.disabled = false;
        }
      }).catch(function() {
        if (typeof notify === 'function') notify('error', 'Request failed');
        self.disabled = false;
      });
    });
  });

  var deleteDialog = document.getElementById('deleteStartupDialog');
  var deleteDialogMessage = document.getElementById('deleteStartupDialogMessage');
  var deleteDialogCancel = document.getElementById('deleteStartupDialogCancel');
  var deleteDialogConfirm = document.getElementById('deleteStartupDialogConfirm');
  var deleteDialogBackdrop = deleteDialog && deleteDialog.querySelector('.dash-dialog-backdrop');
  var pendingDeleteUrl = null;

  function openDeleteDialog(url, name, trigger) {
    pendingDeleteUrl = url;
    if (deleteDialogMessage) deleteDialogMessage.textContent = 'Are you sure you want to delete “' + (name || 'this app') + '”? This cannot be undone.';
    if (deleteDialog && window.EdenDashboardDialog) {
      window.EdenDashboardDialog.open(deleteDialog, trigger);
    }
  }
  function closeDeleteDialog() {
    pendingDeleteUrl = null;
    if (deleteDialog && window.EdenDashboardDialog) {
      window.EdenDashboardDialog.close(deleteDialog);
    }
  }

  document.querySelectorAll('.startup-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var url = this.getAttribute('data-url');
      var name = this.getAttribute('data-name');
      if (url) openDeleteDialog(url, name, this);
    });
  });
  if (deleteDialogCancel) deleteDialogCancel.addEventListener('click', closeDeleteDialog);
  if (deleteDialogBackdrop) deleteDialogBackdrop.addEventListener('click', closeDeleteDialog);
  if (deleteDialogConfirm) {
    deleteDialogConfirm.addEventListener('click', function() {
      if (!pendingDeleteUrl) return;
      var self = this;
      self.disabled = true;
      fetch(pendingDeleteUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _token: csrf })
      }).then(function(r) { return r.json(); }).then(function(data) {
        closeDeleteDialog();
        if (data.status === 'success') {
          if (typeof notify === 'function') notify('success', data.message);
          window.location.reload();
        } else {
          if (typeof notify === 'function') notify('error', data.message || 'Delete failed');
          self.disabled = false;
        }
      }).catch(function() {
        if (typeof notify === 'function') notify('error', 'Request failed');
        self.disabled = false;
      });
    });
  }

  var upvoteDialog = document.getElementById('upvoteDialog');
  var upvoteCount = document.getElementById('upvoteCount');
  var upvoteError = document.getElementById('upvoteError');
  var upvoteMessage = document.getElementById('upvoteDialogMessage');
  var upvoteConfirm = document.getElementById('upvoteDialogConfirm');
  var upvoteCancel = document.getElementById('upvoteDialogCancel');
  var pendingUpvoteUrl = null;
  var pendingUpvoteTrigger = null;

  function closeUpvoteDialog() {
    pendingUpvoteUrl = null;
    if (upvoteDialog && window.EdenDashboardDialog) window.EdenDashboardDialog.close(upvoteDialog);
  }

  document.querySelectorAll('.startup-add-upvotes').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var url = this.getAttribute('data-url');
      var name = this.getAttribute('data-startup') || 'this app';
      if (!url) return;
      pendingUpvoteUrl = url;
      pendingUpvoteTrigger = this;
      upvoteCount.value = '';
      upvoteError.hidden = true;
      upvoteMessage.textContent = 'How many legitimate upvotes should be added for “' + name + '”?';
      window.EdenDashboardDialog.open(upvoteDialog, this);
    });
  });
  if (upvoteCancel) upvoteCancel.addEventListener('click', closeUpvoteDialog);
  if (upvoteDialog) upvoteDialog.querySelector('.dash-dialog-backdrop').addEventListener('click', closeUpvoteDialog);
  if (upvoteConfirm) {
    upvoteConfirm.addEventListener('click', function() {
      var count = Number(upvoteCount.value);
      if (!Number.isInteger(count) || count < 1 || count > 500) {
        upvoteError.hidden = false;
        upvoteCount.focus();
        return;
      }

      var self = pendingUpvoteTrigger;
      var url = pendingUpvoteUrl;
      if (!self || !url) return;
      self.disabled = true;
      upvoteConfirm.disabled = true;
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _token: csrf, count: count })
      }).then(function(r) {
        return r.json().then(function(data) { return { statusCode: r.status, data: data }; });
      }).then(function(res) {
        if (res.statusCode >= 200 && res.statusCode < 300 && res.data.status === 'success') {
          closeUpvoteDialog();
          if (typeof notify === 'function') notify('success', res.data.message || 'Upvotes added');
          window.location.reload();
        } else {
          if (typeof notify === 'function') notify('error', (res.data && res.data.message) ? res.data.message : 'Unable to add upvotes');
          self.disabled = false;
          upvoteConfirm.disabled = false;
        }
      }).catch(function() {
        if (typeof notify === 'function') notify('error', 'Request failed');
        self.disabled = false;
        upvoteConfirm.disabled = false;
      });
    });
  }
})();
</script>
