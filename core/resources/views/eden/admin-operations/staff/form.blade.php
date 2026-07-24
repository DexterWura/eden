<h1 class="dash-page-title">{{ $staff->exists ? 'Edit staff' : 'Add staff' }}</h1>
<form method="post" enctype="multipart/form-data" action="{{ $staff->exists ? route('admin.staff.update', $staff) : route('admin.staff.store') }}" class="dash-card">
@csrf @if($staff->exists) @method('PUT') @endif
<div class="dash-card-body">
  <label>Name <input name="name" value="{{ old('name', $staff->name) }}" required maxlength="255"></label>
  <label>Email <input type="email" name="email" value="{{ old('email', $staff->email) }}" required></label>
  <label>Username <input name="username" value="{{ old('username', $staff->username) }}" required maxlength="255"></label>
  <label>{{ $staff->exists ? 'New password (leave blank to keep)' : 'Password' }} <input type="password" name="password" {{ $staff->exists ? '' : 'required' }} minlength="8"></label>
  <label>Confirm password <input type="password" name="password_confirmation" {{ $staff->exists ? '' : 'required' }}></label>
  @if($staff->exists)<label>Status <select name="status"><option value="1" @selected($staff->status === 1)>Enabled</option><option value="0" @selected($staff->status === 0)>Disabled</option></select></label>@endif
  @if(!$staff->isSuperAdmin())
  <fieldset><legend>Permitted modules</legend>
  @foreach($modules as $module)<label style="display:inline-block;margin:6px 12px 6px 0"><input type="checkbox" name="modules[]" value="{{ $module }}" @checked(in_array($module, old('modules', $staff->allowed_modules ?? []), true))> {{ str_replace('_', ' ', ucfirst($module)) }}</label>@endforeach
  </fieldset>
  @endif
  <button class="dash-btn dash-btn-primary">Save staff account</button>
</div></form>
