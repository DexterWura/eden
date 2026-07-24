<h1 class="dash-page-title">Apps</h1>
<div class="dash-welcome">
  Apps associated with <strong>{{ $user->name }}</strong> ({{ $user->email }}).
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Apps ({{ $startups->count() }})</span>
    <a href="{{ route('admin.users.index') }}" class="dash-btn dash-btn-secondary" style="margin-left: auto; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Back to users</a>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>App</th>
            <th>Category</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($startups as $startup)
          <tr>
            <td>
              <a href="{{ url('/startup/' . $startup->slug) }}" target="_blank" rel="noopener" class="dash-table-link">{{ $startup->name }}</a>
              @if($startup->tagline)
                <div style="font-size: 0.8rem; color: var(--d-text-secondary); margin-top: 2px;">{{ Str::limit($startup->tagline, 50) }}</div>
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
            <td>
              <a href="{{ route('admin.startups.edit', $startup) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none;"><i class="fa-solid fa-pen"></i> Edit</a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="dash-placeholder">This user has no apps.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
.dash-badge-info { background: #dbeafe; color: #1e40af; }
.dash-badge-danger { background: #fee2e2; color: #991b1b; }
</style>
