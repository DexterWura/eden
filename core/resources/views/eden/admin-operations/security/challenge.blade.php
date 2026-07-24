<h1 class="dash-page-title">Two-factor challenge</h1>
<div class="dash-card"><form method="post" action="{{ route('admin.security.challenge.verify') }}" class="dash-card-body">@csrf
  <p>Enter a current authenticator code or one unused recovery code.</p>
  <input name="code" maxlength="32" required autofocus autocomplete="one-time-code">
  <button class="dash-btn dash-btn-primary">Continue</button>
</form></div>
