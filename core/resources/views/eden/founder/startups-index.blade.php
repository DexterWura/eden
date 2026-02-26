<h1 class="dash-page-title">My startups</h1>
<div class="dash-welcome">
  Startups linked to your account. Add a new one or edit details.
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Your startups</span>
    <a href="{{ route('founder.startups.create') }}" class="dash-btn dash-btn-primary" style="margin-left: auto; text-decoration: none;">
      <i class="fa-solid fa-plus"></i> Add startup
    </a>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Startup</th>
            <th>Category</th>
            <th>Upvotes</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($startups as $s)
          <tr>
            <td>
              <a href="{{ url('/startup/' . $s->slug) }}" target="_blank" class="dash-table-link">{{ $s->name }}</a>
              @if($s->tagline)
                <div style="font-size: 0.8rem; color: var(--d-text-secondary);">{{ Str::limit($s->tagline, 50) }}</div>
              @endif
            </td>
            <td>{{ $s->category ?? '—' }}</td>
            <td>{{ $s->upvotes }}</td>
            <td>{{ $s->status ?? 'active' }}</td>
            <td>
              <a href="{{ route('founder.startups.edit', $s) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none;"><i class="fa-solid fa-pen"></i> Edit</a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="dash-placeholder">No startups yet. <a href="{{ route('founder.startups.create') }}">Add your first startup</a>.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
