<h1 class="dash-page-title">Upvotes</h1>
<div class="dash-welcome">
  Total upvotes across your startups and recent upvote activity.
</div>

<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total upvotes</div>
    <div class="dash-kpi-value">{{ $totalUpvotes }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Startups</div>
    <div class="dash-kpi-value">{{ $startups->count() }}</div>
  </div>
</div>

@if($startups->isNotEmpty())
<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header"><span class="dash-card-title">Upvotes by startup</span></div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Startup</th>
            <th>Upvotes</th>
            <th>Link</th>
          </tr>
        </thead>
        <tbody>
          @foreach($startups as $s)
          <tr>
            <td>{{ $s->name }}</td>
            <td>{{ $s->upvotes }}</td>
            <td><a href="{{ url('/startup/' . $s->slug) }}" target="_blank" class="dash-table-link">View page</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header"><span class="dash-card-title">Recent upvotes</span></div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Startup</th>
            <th>When</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentUpvotes as $uv)
          <tr>
            <td>{{ $uv->user->name ?? '—' }}</td>
            <td><a href="{{ url('/startup/' . $uv->startup->slug) }}" class="dash-table-link">{{ $uv->startup->name ?? '—' }}</a></td>
            <td>{{ $uv->created_at?->diffForHumans() ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="3" class="dash-placeholder">No upvote activity yet. Upvotes from the community will appear here.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
