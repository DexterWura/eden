<h1 class="dash-page-title">Ad spots</h1>
<div class="dash-welcome">
  Review and manage self-serve ad spots purchased by guests.
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">All ad spots</span>
  </div>
  <div class="dash-card-body">
    <form method="get" action="{{ route('admin.ad-spots.index') }}" class="dash-startups-filters" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
      <select name="status" class="dash-search" style="max-width: 160px;">
        <option value="">All statuses</option>
        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
      </select>
      <input type="text" name="placement" value="{{ $placement }}" placeholder="Placement key (e.g. home_leaderboard_1)" class="dash-search" style="max-width: 280px;">
      <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
    </form>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Placement</th>
            <th>Status</th>
            <th>Period</th>
            <th>Target URL</th>
            <th>Contact email</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($ads as $ad)
          <tr>
            <td>#{{ $ad->id }}</td>
            <td><code>{{ $ad->placement }}</code></td>
            <td>
              @if($ad->status === \App\Models\AdSpot::STATUS_ACTIVE)
                <span class="dash-badge dash-badge-success">Active</span>
              @elseif($ad->status === \App\Models\AdSpot::STATUS_PENDING)
                <span class="dash-badge dash-badge-warning">Pending</span>
              @else
                <span class="dash-badge">Expired</span>
              @endif
            </td>
            <td>
              @if($ad->starts_at && $ad->ends_at)
                {{ $ad->starts_at->format('M j, Y') }} – {{ $ad->ends_at->format('M j, Y') }}
              @else
                —
              @endif
            </td>
            <td>
              @if($ad->target_url)
                <a href="{{ $ad->target_url }}" target="_blank" rel="noopener noreferrer" class="dash-table-link">Open</a>
              @else
                —
              @endif
            </td>
            <td>{{ $ad->contact_email ?? '—' }}</td>
            <td>{{ $ad->created_at->format('M j, Y H:i') }}</td>
            <td>
              @if($ad->status === \App\Models\AdSpot::STATUS_ACTIVE)
                <form action="{{ route('admin.ad-spots.expire', $ad) }}" method="post" style="display:inline;" onsubmit="return confirm('Expire this ad?');">
                  @csrf
                  <button type="submit" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #dc2626; color: #fff; border: none;">Expire</button>
                </form>
              @elseif($ad->status !== \App\Models\AdSpot::STATUS_ACTIVE)
                <form action="{{ route('admin.ad-spots.activate', $ad) }}" method="post" style="display:inline;" onsubmit="return confirm('Activate this ad for one month?');">
                  @csrf
                  <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Activate</button>
                </form>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="dash-placeholder">No ad spots yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($ads->hasPages())
      <div class="dash-card-footer" style="padding: 12px 16px; border-top: 1px solid var(--d-border);">
        {{ $ads->links() }}
      </div>
    @endif
  </div>
</div>

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
</style>

