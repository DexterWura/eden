<h1 class="dash-page-title">Confirm sensitive action</h1>
<div class="dash-card"><form method="post" action="{{ route('admin.security.reconfirm.verify') }}" class="dash-card-body">@csrf
  <p>Re-enter your password{{ auth('admin')->user()->hasTwoFactorEnabled() ? ' and authentication code' : '' }}. Confirmation remains valid for 10 minutes.</p>
  <input type="password" name="current_password" required autocomplete="current-password" placeholder="Current password">
  @if(auth('admin')->user()->hasTwoFactorEnabled())<input name="code" required autocomplete="one-time-code" placeholder="Authenticator or recovery code">@endif
  <button class="dash-btn dash-btn-primary">Confirm</button>
</form></div>
