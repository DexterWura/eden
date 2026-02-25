<h1 class="dash-page-title">Subscribers</h1>
<div class="dash-welcome">
  Newsletter / list subscribers. Total: <strong>{{ number_format($total) }}</strong>
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Email list</span>
  </div>
  <div class="dash-card-body">
    <form method="get" action="{{ route('admin.subscribers.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
      <input type="text" name="q" value="{{ $search }}" placeholder="Search email…" class="dash-search" style="max-width: 280px;">
      <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Email</th>
            <th>Subscribed</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($subscribers as $subscriber)
          <tr>
            <td>{{ $subscriber->email }}</td>
            <td>{{ $subscriber->created_at?->format('M j, Y H:i') ?? '—' }}</td>
            <td>
              <form action="{{ route('admin.subscribers.destroy', $subscriber) }}" method="post" style="display: inline;" onsubmit="return confirm('Remove this subscriber?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #dc2626; color: #fff; border: none;">Remove</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="3" class="dash-placeholder">No subscribers found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($subscribers->hasPages())
      <div class="dash-card-footer" style="padding: 12px 16px; border-top: 1px solid var(--d-border);">
        {{ $subscribers->links() }}
      </div>
    @endif
  </div>
</div>
