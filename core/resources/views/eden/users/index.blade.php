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
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
          <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="3" class="dash-placeholder">No users found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($users->hasPages())
      <div class="dash-card-footer" style="padding: 12px 16px; border-top: 1px solid var(--d-border);">
        {{ $users->links() }}
      </div>
    @endif
  </div>
</div>
