<header style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
  <div>
    <h1 class="dash-page-title">Notifications</h1>
    <p class="dash-welcome">{{ $unreadCount }} unread {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }}.</p>
  </div>
  @if($unreadCount > 0)
  <form action="{{ route('founder.notifications.read-all') }}" method="post">
    @csrf
    <button class="dash-btn dash-btn-secondary" type="submit"><i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark all read</button>
  </form>
  @endif
</header>

<div class="dash-card">
  <div class="dash-card-body" style="display:flex;flex-direction:column;gap:12px;">
    @forelse($notifications as $notification)
      @php
        $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
        $title = $data['title'] ?? 'Notification';
        $message = $data['message'] ?? '';
        $url = isset($data['url']) && is_string($data['url']) ? $data['url'] : null;
      @endphp
      <article style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1px solid var(--d-border);border-radius:var(--d-radius);{{ $notification->read_at ? '' : 'border-left:4px solid var(--d-primary);' }}">
        <i class="fa-solid fa-bell" aria-hidden="true" style="margin-top:3px;"></i>
        <div style="flex:1;min-width:0;">
          <strong>{{ $title }}</strong>
          @if($message !== '')<p style="margin:4px 0;color:var(--d-text-secondary);">{{ $message }}</p>@endif
          <small style="color:var(--d-text-secondary);">{{ $notification->created_at->diffForHumans() }}</small>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          @if($url)<a href="{{ $url }}" class="dash-btn dash-btn-secondary" style="text-decoration:none;">Open</a>@endif
          @if(!$notification->read_at)
          <form action="{{ route('founder.notifications.read', $notification->id) }}" method="post">
            @csrf
            <button class="dash-btn dash-btn-secondary" type="submit">Mark read</button>
          </form>
          @endif
        </div>
      </article>
    @empty
      <p class="dash-placeholder">You do not have any notifications yet.</p>
    @endforelse
  </div>
</div>

@if($notifications->hasPages())
<nav aria-label="Notification pages" style="margin-top:16px;">{{ $notifications->links() }}</nav>
@endif
