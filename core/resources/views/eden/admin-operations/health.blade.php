<h1 class="dash-page-title">Restricted system health</h1>
<p class="dash-welcome">Configuration names and sanitized diagnostics only. Credentials, raw logs, and exception messages are never displayed.</p>
<div class="dash-card"><div class="dash-table-wrap"><table class="dash-table">
  <tbody>@foreach($checks as $name => $value)<tr><th>{{ str_replace('_', ' ', ucfirst($name)) }}</th><td>{{ $value }}</td></tr>@endforeach</tbody>
</table></div></div>
<div class="dash-card" style="margin-top:16px">
  <div class="dash-card-header"><strong>Sanitized recent errors</strong></div>
  <div class="dash-card-body">
    @forelse($recentErrors as $error)<p><strong>{{ $error['occurred_at'] }}</strong> — {{ $error['summary'] }} <code>{{ $error['fingerprint'] }}</code></p>
    @empty<p class="dash-placeholder">No recent error markers found.</p>@endforelse
  </div>
</div>
