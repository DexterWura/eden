<h1 class="dash-page-title">Audit log</h1>
<form method="get" class="dash-card">
  <div class="dash-card-body" style="display:flex;gap:10px;flex-wrap:wrap">
    <input name="action" value="{{ request('action') }}" placeholder="Action prefix">
    <select name="admin_id"><option value="">All staff</option>@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected((string)request('admin_id') === (string)$admin->id)>{{ $admin->name }}</option>@endforeach</select>
    <input type="date" name="from" value="{{ request('from') }}">
    <input type="date" name="to" value="{{ request('to') }}">
    <button class="dash-btn dash-btn-primary">Filter</button>
  </div>
</form>
<div class="dash-card" style="margin-top:16px"><div class="dash-table-wrap"><table class="dash-table">
  <thead><tr><th>Time</th><th>Staff</th><th>Action</th><th>Description</th><th>Reference</th></tr></thead>
  <tbody>@forelse($logs as $log)<tr>
    <td>{{ $log->created_at }}</td><td>{{ $log->admin?->name ?? 'System' }}</td><td><code>{{ $log->action }}</code></td>
    <td>{{ $log->description }}</td><td>{{ class_basename($log->subject_type ?? '') }} {{ $log->subject_id }}</td>
  </tr>@empty<tr><td colspan="5">No matching audit records.</td></tr>@endforelse</tbody>
</table></div><div class="dash-card-body">{{ $logs->links() }}</div></div>
