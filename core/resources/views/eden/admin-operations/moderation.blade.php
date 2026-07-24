<h1 class="dash-page-title">Moderation queues</h1>
<p class="dash-welcome">Review each queue independently. Bulk operations revalidate every selected record and skip stale items.</p>

@foreach([
  ['startups', 'Pending apps', $pendingStartups, fn($item) => $item->name],
  ['hero', 'Hero requests', $heroRequests, fn($item) => $item->name],
  ['reports', 'Listing reports', $reports, fn($item) => ($item->app?->name ?? 'Deleted app').' — '.$item->reason],
  ['claims', 'Ownership claims', $claims, fn($item) => ($item->app?->name ?? 'Deleted app').' — '.($item->user?->email ?? 'Deleted user')],
] as [$queue, $heading, $items, $label])
<form method="post" action="{{ route('admin.operations.moderation.bulk') }}" class="dash-card" style="margin-bottom:18px">
  @csrf
  <input type="hidden" name="queue" value="{{ $queue }}">
  <div class="dash-card-header"><strong>{{ $heading }}</strong> <span>{{ $items->count() }}</span></div>
  <div class="dash-card-body">
    @forelse($items as $item)
      <label style="display:block;padding:8px 0;border-bottom:1px solid #e5e7eb">
        <input type="checkbox" name="ids[]" value="{{ $item->id }}"> {{ $label($item) }}
      </label>
    @empty
      <p class="dash-placeholder">Queue is clear.</p>
    @endforelse
    @if($items->isNotEmpty())
      <div style="display:flex;gap:8px;margin-top:12px">
        @if(in_array($queue, ['startups']))
          <button class="dash-btn dash-btn-primary" name="action" value="activate">Activate selected</button>
        @else
          <button class="dash-btn dash-btn-primary" name="action" value="approve">Approve selected</button>
        @endif
        <button class="dash-btn" name="action" value="{{ in_array($queue, ['reports','claims']) ? 'dismiss' : 'disable' }}">
          {{ in_array($queue, ['reports','claims']) ? 'Dismiss selected' : 'Disable selected' }}
        </button>
      </div>
    @endif
  </div>
</form>
@endforeach
