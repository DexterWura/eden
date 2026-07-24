<h1 class="dash-page-title">Reports</h1>
<div class="dash-welcome">
  Overview and stats from the database.
</div>

<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total apps</div>
    <div class="dash-kpi-value">{{ $totalStartups }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Active apps</div>
    <div class="dash-kpi-value">{{ $activeStartups }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Featured</div>
    <div class="dash-kpi-value">{{ $featuredStartups }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Launching today</div>
    <div class="dash-kpi-value">{{ $launchingToday }}</div>
  </div>
</div>

<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Registered users</div>
    <div class="dash-kpi-value">{{ $totalUsers }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Subscribers</div>
    <div class="dash-kpi-value">{{ $totalSubscribers }}</div>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">Apps by category</span>
    <a href="{{ route('admin.startups.index') }}" class="dash-table-link">View all</a>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Count</th>
          </tr>
        </thead>
        <tbody>
          @forelse($startupsByCategory as $row)
          <tr>
            <td>{{ $row->category }}</td>
            <td>{{ $row->count }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="2" class="dash-placeholder">No categories yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">Recent apps</span>
    <a href="{{ route('admin.startups.index') }}" class="dash-table-link">View all</a>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>App</th>
            <th>Category</th>
            <th>Status</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentStartups as $startup)
          <tr>
            <td><a href="{{ url('/startup/' . $startup->slug) }}" target="_blank" class="dash-table-link">{{ $startup->name }}</a></td>
            <td>{{ $startup->category ?? '—' }}</td>
            <td>{{ $startup->status ?? 'active' }}</td>
            <td>{{ $startup->created_at?->format('M j, Y') ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="dash-placeholder">No apps yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
