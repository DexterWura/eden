<h1 class="dash-page-title">Analytics</h1>
<div class="dash-welcome" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
  <span><strong>Pro Analytics</strong> — Track views, revenue, upvotes, comments, and more across your startups. Stock-market style charts for quick insights.</span>
  <div class="analytics-export-wrap" style="display: flex; align-items: center; gap: 8px;">
    <a href="{{ route('founder.analytics.investor-update') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none; font-size: 0.875rem;"><i class="fa-solid fa-envelope-open-text"></i> Investor update</a>
    <a href="{{ route('founder.analytics.export.csv') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none; font-size: 0.875rem;"><i class="fa-solid fa-file-csv"></i> CSV</a>
    <a href="{{ route('founder.analytics.export.pdf') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none; font-size: 0.875rem;"><i class="fa-solid fa-file-pdf"></i> PDF</a>
    <button type="button" id="analyticsExportPng" class="dash-btn dash-btn-secondary" style="font-size: 0.875rem;"><i class="fa-solid fa-image"></i> PNG</button>
  </div>
</div>

@php
  $totalViews = $totalViews ?? 0;
  $totalClicks = $totalClicks ?? 0;
  $totalUpvotes = $totalUpvotes ?? 0;
  $totalComments = $totalComments ?? 0;
  $totalRevenue = $totalRevenue ?? 0;
  $totalMrr = $totalMrr ?? 0;
  $startupMetrics = $startupMetrics ?? [];
  $revenueByDay = $revenueByDay ?? [];
  $upvotesByDay = $upvotesByDay ?? [];
  $commentsByDay = $commentsByDay ?? [];
  $days = $days ?? 60;
  $dateLabels = $dateLabels ?? [];
@endphp

<div class="analytics-kpi-grid">
  <div class="analytics-kpi analytics-kpi--views">
    <div class="analytics-kpi-icon"><i class="fa-solid fa-eye"></i></div>
    <div class="analytics-kpi-content">
      <span class="analytics-kpi-label">Total views</span>
      <span class="analytics-kpi-value">{{ number_format($totalViews) }}</span>
    </div>
  </div>
  <div class="analytics-kpi analytics-kpi--clicks">
    <div class="analytics-kpi-icon"><i class="fa-solid fa-arrow-pointer"></i></div>
    <div class="analytics-kpi-content">
      <span class="analytics-kpi-label">Clicks</span>
      <span class="analytics-kpi-value">{{ number_format($totalClicks) }}</span>
    </div>
  </div>
  <div class="analytics-kpi analytics-kpi--upvotes">
    <div class="analytics-kpi-icon"><i class="fa-solid fa-arrow-up"></i></div>
    <div class="analytics-kpi-content">
      <span class="analytics-kpi-label">Upvotes</span>
      <span class="analytics-kpi-value">{{ number_format($totalUpvotes) }}</span>
    </div>
  </div>
  <div class="analytics-kpi analytics-kpi--comments">
    <div class="analytics-kpi-icon"><i class="fa-solid fa-comment"></i></div>
    <div class="analytics-kpi-content">
      <span class="analytics-kpi-label">Comments</span>
      <span class="analytics-kpi-value">{{ number_format($totalComments) }}</span>
    </div>
  </div>
  <div class="analytics-kpi analytics-kpi--revenue">
    <div class="analytics-kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
    <div class="analytics-kpi-content">
      <span class="analytics-kpi-label">Total revenue</span>
      <span class="analytics-kpi-value">${{ number_format($totalRevenue, 2) }}</span>
    </div>
  </div>
  <div class="analytics-kpi analytics-kpi--mrr">
    <div class="analytics-kpi-icon"><i class="fa-solid fa-chart-line"></i></div>
    <div class="analytics-kpi-content">
      <span class="analytics-kpi-label">MRR</span>
      <span class="analytics-kpi-value">${{ number_format($totalMrr, 2) }}</span>
    </div>
  </div>
</div>

@if(count($startupMetrics) === 0)
<div class="dash-card">
  <div class="dash-card-body" style="text-align: center; padding: 48px 24px;">
    <p style="color: var(--d-text-secondary); font-size: 1rem; margin-bottom: 16px;">Add a startup to see analytics.</p>
    <a href="{{ url('/founder/startups/create') }}" class="dash-btn dash-btn-primary" style="text-decoration: none;"><i class="fa-solid fa-plus"></i> Add startup</a>
  </div>
</div>
@else
<div class="analytics-charts">
  <div class="dash-card analytics-chart-card">
    <div class="dash-card-header">
      <span class="dash-card-title"><i class="fa-solid fa-chart-area"></i> Revenue over time</span>
      <span class="dash-card-subtitle">Last {{ $days }} days · cumulative</span>
    </div>
    <div class="dash-card-body">
      <div id="revenueChart" class="analytics-chart"></div>
    </div>
  </div>

  <div class="analytics-chart-row">
    <div class="dash-card analytics-chart-card analytics-chart-card--half">
      <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-arrow-up"></i> Upvotes by day</span>
      </div>
      <div class="dash-card-body">
        <div id="upvotesChart" class="analytics-chart"></div>
      </div>
    </div>
    <div class="dash-card analytics-chart-card analytics-chart-card--half">
      <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-comment"></i> Comments by day</span>
      </div>
      <div class="dash-card-body">
        <div id="commentsChart" class="analytics-chart"></div>
      </div>
    </div>
  </div>

  <div class="dash-card analytics-chart-card">
    <div class="dash-card-header">
      <span class="dash-card-title"><i class="fa-solid fa-bars-progress"></i> Per-startup comparison</span>
    </div>
    <div class="dash-card-body">
      <div id="startupComparisonChart" class="analytics-chart"></div>
    </div>
  </div>

  <div class="dash-card analytics-chart-card">
    <div class="dash-card-header">
      <span class="dash-card-title"><i class="fa-solid fa-table"></i> Metrics by startup</span>
    </div>
    <div class="dash-card-body" style="padding: 0;">
      <div class="dash-table-wrap">
        <table class="dash-table analytics-metrics-table">
          <thead>
            <tr>
              <th>Startup</th>
              <th>Views</th>
              <th>Clicks</th>
              <th>Upvotes</th>
              <th>Comments</th>
              <th>Revenue</th>
              <th>MRR</th>
            </tr>
          </thead>
          <tbody>
            @foreach($startupMetrics as $m)
            <tr>
              <td><a href="{{ url('/startup/' . $m['slug']) }}" target="_blank" class="dash-table-link">{{ $m['name'] }}</a></td>
              <td>{{ number_format($m['views']) }}</td>
              <td>{{ number_format($m['clicks']) }}</td>
              <td>{{ number_format($m['upvotes']) }}</td>
              <td>{{ number_format($m['comments']) }}</td>
              <td>${{ number_format($m['revenue'], 2) }}</td>
              <td>${{ number_format($m['mrr'], 2) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var isDark = document.body.classList.contains('dashboard') ? false : (document.documentElement.getAttribute('data-theme') !== 'light');
  var textColor = isDark ? '#8b90a0' : '#5f6368';
  var gridColor = isDark ? '#2a2e3d' : '#e8eaed';
  var accentColor = '#00d4aa';
  var chartTheme = {
    mode: isDark ? 'dark' : 'light',
    palette: 'palette1',
    monochrome: { enabled: true, color: accentColor, shadeTo: 'light', shadeIntensity: 0.4 }
  };

  function cumulativeFromDaily(dates, dailyData) {
    var cum = 0;
    return dates.map(function(d) {
      cum += (parseFloat(dailyData[d]) || 0);
      return cum;
    });
  }

  var dates = @json($dateLabels);
  var revenueDaily = @json($revenueByDay);
  var upvotesDaily = @json($upvotesByDay);
  var commentsDaily = @json($commentsByDay);

  var revenueSeries = cumulativeFromDaily(dates, revenueDaily);
  var upvotesSeries = dates.map(function(d) { return parseInt(upvotesDaily[d] || 0, 10); });
  var commentsSeries = dates.map(function(d) { return parseInt(commentsDaily[d] || 0, 10); });

  var commonOptions = {
    chart: { fontFamily: 'Outfit, system-ui, sans-serif', background: 'transparent' },
    colors: [accentColor],
    grid: { borderColor: gridColor, strokeDashArray: 3 },
    xaxis: { labels: { style: { colors: textColor } } },
    yaxis: { labels: { style: { colors: textColor } } },
    tooltip: { theme: isDark ? 'dark' : 'light' }
  };

  var chartInstances = [];

  if (document.getElementById('revenueChart')) {
    var ch = new ApexCharts(document.getElementById('revenueChart'), {
      series: [{ name: 'Revenue', data: revenueSeries }],
      chart: { type: 'area', height: 280, toolbar: { show: true }, zoom: { enabled: true } },
      stroke: { curve: 'smooth', width: 2 },
      fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
      dataLabels: { enabled: false },
      xaxis: { categories: dates.map(function(d) { return d.slice(5); }) },
      ...commonOptions
    });
    chartInstances.push({ chart: ch, name: 'eden-revenue' });
    ch.render();
  }

  if (document.getElementById('upvotesChart')) {
    var ch = new ApexCharts(document.getElementById('upvotesChart'), {
      series: [{ name: 'Upvotes', data: upvotesSeries }],
      chart: { type: 'bar', height: 240 },
      plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
      dataLabels: { enabled: false },
      xaxis: { categories: dates.map(function(d) { return d.slice(5); }) },
      ...commonOptions
    });
    chartInstances.push({ chart: ch, name: 'eden-upvotes' });
    ch.render();
  }

  if (document.getElementById('commentsChart')) {
    var ch = new ApexCharts(document.getElementById('commentsChart'), {
      series: [{ name: 'Comments', data: commentsSeries }],
      chart: { type: 'bar', height: 240 },
      plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
      dataLabels: { enabled: false },
      xaxis: { categories: dates.map(function(d) { return d.slice(5); }) },
      colors: ['#6366f1'],
      ...commonOptions
    });
    chartInstances.push({ chart: ch, name: 'eden-comments' });
    ch.render();
  }

  var metrics = @json($startupMetrics);
  if (document.getElementById('startupComparisonChart') && metrics.length > 0) {
    var names = metrics.map(function(m) { return m.name.length > 18 ? m.name.slice(0, 15) + '…' : m.name; });
    var ch = new ApexCharts(document.getElementById('startupComparisonChart'), {
      series: [
        { name: 'Views', data: metrics.map(function(m) { return m.views; }) },
        { name: 'Upvotes', data: metrics.map(function(m) { return m.upvotes; }) },
        { name: 'Clicks', data: metrics.map(function(m) { return m.clicks; }) },
        { name: 'Comments', data: metrics.map(function(m) { return m.comments; }) }
      ],
      chart: { type: 'bar', height: 320, stacked: false },
      plotOptions: { bar: { horizontal: false, columnWidth: '70%', borderRadius: 4 } },
      xaxis: { categories: names },
      legend: { position: 'top' },
      colors: [accentColor, '#6366f1', '#f59e0b', '#10b981'],
      ...commonOptions
    });
    chartInstances.push({ chart: ch, name: 'eden-comparison' });
    ch.render();
  }

  function downloadUri(uri, filename) {
    var a = document.createElement('a');
    a.href = uri;
    a.download = filename;
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }

  document.getElementById('analyticsExportPng')?.addEventListener('click', function() {
    if (chartInstances.length === 0) return;
    var btn = this;
    btn.disabled = true;
    btn.textContent = 'Preparing…';
    var i = 0;
    function next() {
      if (i >= chartInstances.length) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-image"></i> PNG';
        return;
      }
      var item = chartInstances[i];
      item.chart.dataURI().then(function(opts) {
        downloadUri(opts.imgURI, item.name + '.png');
        i++;
        setTimeout(next, 300);
      }).catch(function() {
        i++;
        next();
      });
    }
    next();
  });
})();
</script>
@endif
