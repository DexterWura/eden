<h1 class="dash-page-title">Admin profile & security</h1>
@if(session('recovery_codes'))
<div class="dash-card"><div class="dash-card-header"><strong>Save these one-time recovery codes</strong></div><div class="dash-card-body"><pre>{{ implode("\n", session('recovery_codes')) }}</pre></div></div>
@endif
<div class="dash-card"><div class="dash-card-header"><strong>Profile</strong></div><form method="post" action="{{ route('admin.security.profile.update') }}" class="dash-card-body">@csrf @method('PUT')
  <label>Name <input name="name" value="{{ old('name', $admin->name) }}" required maxlength="120"></label>
  <label>Email <input type="email" name="email" value="{{ old('email', $admin->email) }}" required></label>
  <button class="dash-btn dash-btn-primary">Save profile</button>
</form></div>
<div class="dash-card" style="margin-top:16px"><div class="dash-card-header"><strong>Password</strong></div><form method="post" action="{{ route('admin.security.password') }}" class="dash-card-body">@csrf @method('PUT')
  <input type="password" name="current_password" placeholder="Current password" required autocomplete="current-password">
  <input type="password" name="password" placeholder="New password (12+ characters)" required autocomplete="new-password">
  <input type="password" name="password_confirmation" placeholder="Confirm new password" required autocomplete="new-password">
  <button class="dash-btn dash-btn-primary">Change password</button>
</form></div>
<div class="dash-card" style="margin-top:16px"><div class="dash-card-header"><strong>Authenticator app (TOTP)</strong></div><div class="dash-card-body">
@if($admin->hasTwoFactorEnabled())
  <p>Two-factor authentication is enabled.</p>
  <form method="post" action="{{ route('admin.security.2fa.disable') }}">@csrf @method('DELETE')
    <input type="password" name="current_password" placeholder="Current password" required>
    <input name="code" placeholder="Authenticator or recovery code" required>
    <button class="dash-btn">Disable 2FA</button>
  </form>
@else
  <form method="post" action="{{ route('admin.security.2fa.begin') }}">@csrf
    <input type="password" name="current_password" placeholder="Current password" required>
    <button class="dash-btn dash-btn-primary">Set up 2FA</button>
  </form>
@endif
</div></div>
