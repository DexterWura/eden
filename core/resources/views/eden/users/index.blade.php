<h1 class="dash-page-title">Users</h1>
<div class="dash-welcome">
  Registered users (founders and visitors with accounts).
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">All users</span>
  </div>
  <div class="dash-card-body">
    <form method="get" action="{{ route('admin.users.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
      <input type="text" name="q" value="{{ $search }}" placeholder="Search name or email…" class="dash-search" style="max-width: 280px;">
      <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Joined</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
          @php
            $isActive = (int)($user->status ?? 1) === 1;
          @endphp
          <tr>
            <td>{{ $user->name }}</td>
            <td>
              <div>{{ $user->email }}</div>
              @if($linkedinConfigured && !empty(trim($user->linkedin_url ?? '')))
                <form action="{{ route('admin.users.feature-on-hero', $user) }}" method="post" style="display: inline-block; margin-top: 6px;">
                  @csrf
                  @if($user->featured_on_hero)
                    <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Unfeature</button>
                  @else
                    <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Feature on hero</button>
                  @endif
                </form>
              @endif
            </td>
            <td>{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
            <td>
              @if($isActive)
                <span class="dash-badge dash-badge-success">Enabled</span>
              @else
                <span class="dash-badge dash-badge-danger">Disabled</span>
              @endif
            </td>
            <td>
              <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="post" style="display: inline;">
                  @csrf
                  @if($isActive)
                    <button type="submit" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #dc2626; color: #fff; border: none;">Disable</button>
                  @else
                    <button type="submit" class="dash-btn dash-btn-primary" style="padding: 4px 10px; font-size: 0.8rem;">Enable</button>
                  @endif
                </form>
                <a href="{{ route('admin.users.startups', $user) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none;"><i class="fa-solid fa-rocket"></i> Startups</a>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="dash-placeholder">No users found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($users->hasPages())
      <div class="dash-card-footer">
        {{ $users->links() }}
      </div>
    @endif
  </div>
</div>

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-danger { background: #fee2e2; color: #991b1b; }
</style>
