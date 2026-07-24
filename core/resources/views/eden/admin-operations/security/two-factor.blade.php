<h1 class="dash-page-title">Enable two-factor authentication</h1>
<div class="dash-card"><div class="dash-card-body">
  <p>Add this account to any RFC 6238-compatible authenticator using the secret or provisioning URI.</p>
  <p><strong>Secret:</strong> <code>{{ $secret }}</code></p>
  <details><summary>Provisioning URI</summary><code style="word-break:break-all">{{ $provisioningUri }}</code></details>
  <form method="post" action="{{ route('admin.security.2fa.confirm') }}" style="margin-top:16px">@csrf
    <input name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="6-digit code" required autocomplete="one-time-code">
    <button class="dash-btn dash-btn-primary">Verify and enable</button>
  </form>
</div></div>
