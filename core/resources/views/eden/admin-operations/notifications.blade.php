<h1 class="dash-page-title">Admin notification centre</h1>
<form method="post" action="{{ route('admin.operations.notifications.read') }}">
  @csrf
  <div class="dash-card">
    <div class="dash-card-header"><strong>Database notifications</strong><button class="dash-btn">Mark selected read</button></div>
    <div class="dash-card-body">
      @forelse($notifications as $notification)
      <label style="display:block;padding:12px;border-bottom:1px solid #e5e7eb;opacity:{{ $notification->read_at ? '.65' : '1' }}">
        <input type="checkbox" name="ids[]" value="{{ $notification->id }}">
        <strong>{{ $notification->title }}</strong> <small>{{ $notification->created_at->diffForHumans() }}</small>
        <span style="display:block">{{ $notification->message }}</span>
        @if($notification->action_url)<a href="{{ $notification->action_url }}">Open</a>@endif
      </label>
      @empty<p class="dash-placeholder">No admin notifications.</p>@endforelse
    </div>
  </div>
</form>
{{ $notifications->links() }}
