<h1 class="dash-page-title">Startup website health</h1>
<div class="dash-welcome">
  All startups and whether their website URL is reachable. The system pings each startup&rsquo;s website every 3 days.
  After 3 consecutive ping failures, a startup is marked <strong>dormant</strong>. Dormant startups are deleted automatically after 30 days if they remain dormant.
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">All startups</span>
    <form action="{{ route('admin.startup-websites.run-check') }}" method="post" style="display: inline;">
      @csrf
      <button type="submit" class="dash-btn dash-btn-secondary">Check due now</button>
    </form>
    <form action="{{ route('admin.startup-websites.run-check') }}" method="post" style="display: inline;">
      @csrf
      <input type="hidden" name="force" value="1">
      <button type="submit" class="dash-btn dash-btn-primary">Check all (force)</button>
    </form>
  </div>
  <div class="dash-card-body">
    <form method="get" action="{{ route('admin.startup-websites.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
      <select name="filter" class="dash-search" style="max-width: 200px;">
        <option value="">All startups</option>
        <option value="with-website" {{ $filter === 'with-website' ? 'selected' : '' }}>With website only</option>
        <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Active only</option>
        <option value="dormant" {{ $filter === 'dormant' ? 'selected' : '' }}>Dormant only</option>
      </select>
      <button type="submit" class="dash-btn dash-btn-secondary">Filter</button>
    </form>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Startup</th>
            <th>Website</th>
            <th>List status</th>
            <th>Website status</th>
            <th>Last checked</th>
            <th>Failures</th>
          </tr>
        </thead>
        <tbody>
          @forelse($startups as $startup)
          <tr>
            <td>
              <a href="{{ route('admin.startups.edit', $startup) }}" class="dash-table-link">{{ $startup->name }}</a>
            </td>
            <td>
              @if($startup->website)
                <a href="{{ $startup->website }}" target="_blank" rel="noopener" class="dash-table-link" style="word-break: break-all;">{{ Str::limit($startup->website, 40) }}</a>
              @else
                <span style="color: var(--d-text-secondary);">—</span>
              @endif
            </td>
            <td>
              @if($startup->status === 'active')
                <span class="dash-badge dash-badge-success">Active</span>
              @elseif($startup->status === 'disabled')
                <span class="dash-badge dash-badge-warning">Disabled</span>
              @elseif($startup->status === 'dormant')
                <span class="dash-badge dash-badge-danger">Dormant</span>
              @else
                <span class="dash-badge dash-badge-danger">Banned</span>
              @endif
            </td>
            <td>
              @if(!$startup->website)
                <span style="color: var(--d-text-secondary);">No URL</span>
              @elseif($startup->website_last_checked_at === null)
                <span class="dash-badge dash-badge-muted">Not checked</span>
              @elseif($startup->website_is_reachable)
                <span class="dash-badge dash-badge-success">Reachable</span>
              @else
                <span class="dash-badge dash-badge-danger">Unreachable</span>
              @endif
            </td>
            <td>
              @if($startup->website_last_checked_at)
                <span title="{{ $startup->website_last_checked_at->format('Y-m-d H:i:s') }}">{{ $startup->website_last_checked_at->diffForHumans() }}</span>
              @else
                <span style="color: var(--d-text-secondary);">—</span>
              @endif
            </td>
            <td>
              @if($startup->website)
                {{ (int) ($startup->website_consecutive_failures ?? 0) }}
              @else
                —
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="dash-placeholder">No startups found.</td>
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

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
.dash-badge-danger { background: #fee2e2; color: #991b1b; }
.dash-badge-muted { background: #e5e7eb; color: #374151; }
</style>
