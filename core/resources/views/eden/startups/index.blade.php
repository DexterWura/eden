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
                  <button type="button" class="dash-btn dash-btn-primary startup-action" style="padding: 4px 10px; font-size: 0.8rem;" data-action="activate" data-url="{{ route('admin.startups.activate', $startup) }}">Activate</button>
                @endif
                @if($startup->isBanned())
                  <button type="button" class="dash-btn dash-btn-primary startup-action" style="padding: 4px 10px; font-size: 0.8rem;" data-action="unban" data-url="{{ route('admin.startups.unban', $startup) }}">Unban</button>
                @else
                  <button type="button" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #dc2626; color: #fff; border: none;" data-action="ban" data-url="{{ route('admin.startups.ban', $startup) }}">Ban</button>
                @endif
                <button type="button" class="dash-btn dash-btn-secondary startup-action startup-featured" style="padding: 4px 10px; font-size: 0.8rem;" data-url="{{ route('admin.startups.toggle-featured', $startup) }}" data-featured="{{ $startup->is_featured ? '1' : '0' }}">
                  {{ $startup->is_featured ? 'Unfeature' : 'Feature' }}
                </button>
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
      <div class="dash-card-footer" style="padding: 12px 16px; border-top: 1px solid var(--d-border);">
        {{ $startups->links() }}
      </div>
    @endif
  </div>
</div>

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
.dash-badge-danger { background: #fee2e2; color: #991b1b; }
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
})();
</script>
