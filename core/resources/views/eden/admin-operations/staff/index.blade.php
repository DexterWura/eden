<h1 class="dash-page-title">Staff & role access</h1>
<p class="dash-welcome">Only super administrators can create staff or change module access.</p>
<div class="dash-card"><div class="dash-card-header"><strong>Admin accounts</strong><a class="dash-btn dash-btn-primary" href="{{ route('admin.staff.create') }}">Add staff</a></div>
<div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Modules</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@foreach($admins as $staff)<tr>
  <td>{{ $staff->name }}<br><small>{{ $staff->email }}</small></td><td>{{ $staff->username }}</td>
  <td>{{ $staff->isSuperAdmin() ? 'Super admin' : 'Staff' }}</td><td>{{ $staff->isSuperAdmin() ? 'All' : implode(', ', $staff->allowed_modules ?? []) }}</td>
  <td>{{ $staff->isEnabled() ? 'Enabled' : 'Disabled' }}</td><td><a class="dash-btn" href="{{ route('admin.staff.edit', $staff) }}">Edit</a>
  @if($staff->id !== auth('admin')->id())<form method="post" action="{{ route('admin.staff.status', $staff) }}" style="display:inline">@csrf<button class="dash-btn">Toggle status</button></form>
  <form method="post" action="{{ route('admin.staff.destroy', $staff) }}" style="display:inline" data-confirm="Delete this staff account?">@csrf @method('DELETE')<button class="dash-btn">Delete</button></form>@endif</td>
</tr>@endforeach
</tbody></table></div><div class="dash-card-body">{{ $admins->links() }}</div></div>
