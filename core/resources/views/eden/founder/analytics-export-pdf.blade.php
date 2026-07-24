<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics Export — {{ $siteName ?? 'Eden' }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; padding: 20px; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    .meta { color: #666; font-size: 10px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
    th { background: #f5f5f5; font-weight: 600; }
    .kpi-row { margin-bottom: 16px; }
    .kpi-row span { display: inline-block; width: 140px; font-weight: 600; }
    .kpi-row .val { width: auto; font-weight: normal; }
  </style>
</head>
<body>
  <h1>{{ $siteName ?? 'Eden' }} — Analytics Report</h1>
  <p class="meta">Exported {{ $exportedAt ?? now()->toDateTimeString() }}</p>

  <h2 style="font-size: 14px; margin-bottom: 10px;">Summary</h2>
  <div class="kpi-row"><span>Total views</span><span class="val">{{ number_format($totalViews ?? 0) }}</span></div>
  <div class="kpi-row"><span>Total clicks</span><span class="val">{{ number_format($totalClicks ?? 0) }}</span></div>
  <div class="kpi-row"><span>Total upvotes</span><span class="val">{{ number_format($totalUpvotes ?? 0) }}</span></div>
  <div class="kpi-row"><span>Total comments</span><span class="val">{{ number_format($totalComments ?? 0) }}</span></div>
  <div class="kpi-row"><span>Total revenue</span><span class="val">${{ number_format($totalRevenue ?? 0, 2) }}</span></div>
  <div class="kpi-row"><span>Total MRR</span><span class="val">${{ number_format($totalMrr ?? 0, 2) }}</span></div>

  @if(!empty($startupMetrics))
  <h2 style="font-size: 14px; margin: 24px 0 10px;">Metrics by app</h2>
  <table>
    <thead>
      <tr>
        <th>App</th>
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
        <td>{{ $m['name'] }}</td>
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
  @endif

  <p class="meta" style="margin-top: 30px;">Pro member export. Last {{ $days ?? 60 }} days of data.</p>
</body>
</html>
