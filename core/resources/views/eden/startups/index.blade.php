<h1 class="dash-page-title">Startups</h1>
<div class="dash-welcome">
  Manage startups: view founders, disable, ban, activate, set featured, and edit details.
</div>

<div class="dash-kpi-row">
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
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">All startups</span>
    <a href="{{ route('admin.startups.create') }}" class="dash-btn dash-btn-primary" style="margin-left: auto;">
      <i class="fa-solid fa-plus"></i> Add startup
    </a>
  </div>
  <div class="dash-card-body">
    <form method="get" action="{{ route('admin.startups.index') }}" class="dash-startups-filters" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
      <input type="text" name="q" value="{{ $search }}" placeholder="Search name, founder, category…" class="dash-search" style="max-width: 260px;">
      <select name="status" class="dash-search" style="max-width: 160px;">
        <option value="">All statuses</option>
        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
        <option value="disabled" {{ $statusFilter === 'disabled' ? 'selected' : '' }}>Disabled</option>
        <option value="banned" {{ $statusFilter === 'banned' ? 'selected' : '' }}>Banned</option>
        <option value="dormant" {{ $statusFilter === 'dormant' ? 'selected' : '' }}>Dormant</option>
      </select>
      <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
    </form>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Startup</th>
            <th>Founder</th>
            <th>Category</th>
            <th>Status</th>
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
              @if($startup->status === 'active')
                <span class="dash-badge dash-badge-success">Active</span>
              @elseif($startup->status === 'disabled')
                <span class="dash-badge dash-badge-warning">Disabled</span>
              @elseif($startup->status === 'dormant')
                <span class="dash-badge dash-badge-info">Dormant</span>
              @else
                <span class="dash-badge dash-badge-danger">Banned</span>
              @endif
            </td>
            <td>{{ $startup->is_featured ? 'Yes' : '—' }}</td>
            <td>{{ $startup->upvotes }}</td>
            <td>
              <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                <a href="{{ route('admin.startups.edit', $startup) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;"><i class="fa-solid fa-pen"></i> Edit</a>
                @if($startup->isActive())
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
                @php
                  $featureUser = $startup->user ?? $startup->heroUser ?? null;
                  $hasFounderLinkedIn = !empty(trim($startup->founder_linkedin_url ?? ''));
                  if (!$hasFounderLinkedIn) {
                    foreach ($startup->founders ?? [] as $_f) {
                      $fLi = is_array($_f) ? ($_f['linkedin_url'] ?? null) : ($_f->linkedin_url ?? null);
                      if (!empty(trim((string)($fLi ?? '')))) { $hasFounderLinkedIn = true; break; }
                    }
                  }
                @endphp
                @if($featureUser && $hasFounderLinkedIn)
                  <form action="{{ route('admin.users.feature-on-hero', $featureUser) }}" method="post" style="display: inline;">
                    @csrf
                    @if($featureUser->featured_on_hero)
                      <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Unfeature on hero</button>
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
            <td colspan="7" class="dash-placeholder">No startups found.</td>
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

<div id="deleteStartupDialog" class="dash-dialog" role="dialog" aria-modal="true" aria-labelledby="deleteStartupDialogTitle" hidden>
  <div class="dash-dialog-backdrop"></div>
  <div class="dash-dialog-box" style="max-width: 400px;">
    <h2 id="deleteStartupDialogTitle" class="dash-dialog-title">Delete startup</h2>
    <p class="dash-dialog-body" id="deleteStartupDialogMessage">Are you sure you want to delete this startup? This cannot be undone.</p>
    <div class="dash-dialog-actions" style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
      <button type="button" class="dash-btn dash-btn-secondary" id="deleteStartupDialogCancel">Cancel</button>
      <button type="button" class="dash-btn" id="deleteStartupDialogConfirm" style="background: #991b1b; color: #fff; border: none;">Delete</button>
    </div>
  </div>
</div>

<style>
.dash-dialog { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 16px; }
.dash-dialog[hidden] { display: none; }
.dash-dialog-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
.dash-dialog-box { position: relative; background: var(--d-bg, #fff); border-radius: 8px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
.dash-dialog-title { margin: 0 0 8px; font-size: 1.125rem; }
.dash-dialog-body { margin: 0; color: var(--d-text-secondary, #64748b); font-size: 0.9375rem; }
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
.dash-badge-danger { background: #fee2e2; color: #991b1b; }
.dash-badge-info { background: #dbeafe; color: #1e40af; }
</style>

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

  function openDeleteDialog(url, name) {
    pendingDeleteUrl = url;
    if (deleteDialogMessage) deleteDialogMessage.textContent = 'Are you sure you want to delete “' + (name || 'this startup') + '”? This cannot be undone.';
    if (deleteDialog) { deleteDialog.removeAttribute('hidden'); }
  }
  function closeDeleteDialog() {
    pendingDeleteUrl = null;
    if (deleteDialog) { deleteDialog.setAttribute('hidden', ''); }
  }

  document.querySelectorAll('.startup-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var url = this.getAttribute('data-url');
      var name = this.getAttribute('data-name');
      if (url) openDeleteDialog(url, name);
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
})();
</script>
