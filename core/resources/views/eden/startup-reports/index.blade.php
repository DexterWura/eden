<h1 class="dash-page-title">Listing reports</h1>
<div class="dash-welcome">
  Visitor reports about startup listings (spam, wrong category, impersonation, etc.).
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Reports</span>
    <form method="get" action="{{ route('admin.startup-reports.index') }}" class="dash-startups-filters" style="display: flex; flex-wrap: wrap; gap: 12px; margin-left: auto;">
      <select name="status" class="dash-search" style="max-width: 180px;">
        <option value="">All statuses</option>
        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="reviewed" {{ $status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
        <option value="dismissed" {{ $status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
      </select>
      <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-filter"></i> Apply</button>
    </form>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr id="report-{{ $r->id }}">
            <th>Date</th>
            <th>Startup</th>
            <th>Reason</th>
            <th>Reporter</th>
            <th>Details</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reports as $r)
          <tr>
            <td style="white-space: nowrap;">{{ $r->created_at->format('M j, Y H:i') }}</td>
            <td>
              @if($r->startup)
                <a href="{{ url('/startup/' . $r->startup->slug) }}" target="_blank" rel="noopener noreferrer" class="dash-table-link">{{ $r->startup->name }}</a>
              @else
                —
              @endif
            </td>
            <td>{{ $reasonLabels[$r->reason] ?? $r->reason }}</td>
            <td><a href="mailto:{{ $r->reporter_email }}">{{ $r->reporter_email }}</a></td>
            <td style="max-width: 220px;">
              {{ $r->details ? Str::limit($r->details, 100) : '—' }}
              @if($r->admin_notes)
                <div style="margin-top: 4px; font-size: 0.75rem; color: var(--d-text-secondary);"><strong>Notes:</strong> {{ Str::limit($r->admin_notes, 80) }}</div>
              @endif
            </td>
            <td>
              @if($r->status === \App\Models\StartupReport::STATUS_PENDING)
                <span class="dash-badge dash-badge-warning">Pending</span>
              @elseif($r->status === \App\Models\StartupReport::STATUS_REVIEWED)
                <span class="dash-badge dash-badge-success">Reviewed</span>
              @else
                <span class="dash-badge">Dismissed</span>
              @endif
            </td>
            <td style="white-space: nowrap;">
              @if($r->status === \App\Models\StartupReport::STATUS_PENDING)
                <form action="{{ route('admin.startup-reports.resolve', $r) }}" method="post" style="display:inline;" data-confirm="Mark this report as reviewed?" data-confirm-label="Mark reviewed">
                  @csrf
                  <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Reviewed</button>
                </form>
                <form action="{{ route('admin.startup-reports.dismiss', $r) }}" method="post" style="display:inline;" data-confirm="Dismiss this report without further action?" data-confirm-label="Dismiss report">
                  @csrf
                  <button type="submit" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #64748b; color: #fff; border: none;">Dismiss</button>
                </form>
              @else
                <span style="font-size: 0.75rem; color: var(--d-text-secondary);">{{ $r->reviewed_at?->diffForHumans() ?? '—' }}</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="dash-placeholder">No reports yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($reports->hasPages())
  <div class="dash-card-footer" style="padding: 12px 16px; border-top: 1px solid var(--d-border);">
    {{ $reports->links() }}
  </div>
  @endif
</div>

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
</style>
