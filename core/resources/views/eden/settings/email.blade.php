<h1 class="dash-page-title">Email settings</h1>
<div class="dash-welcome">
  Configure how Eden sends emails, the global HTML wrapper, and the templates used for welcome and verification emails.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Email transport (SMTP / provider)</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Choose how emails are sent and, for SMTP, configure your mail server. These settings apply to all emails sent by Eden.</p>
    <form action="{{ route('admin.settings.email.update') }}" method="post" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 20px;">
        <div style="display: grid; grid-template-columns: minmax(0, 260px) minmax(0, 1fr); gap: 16px; align-items: start;">
          <div>
            <label for="email_method" class="dash-label">Email method</label>
            @php
              $method = old('email_method', isset($mailConfig->name) ? (string) $mailConfig->name : 'smtp');
            @endphp
            <select id="email_method" name="email_method" class="dash-input">
              <option value="php" {{ $method === 'php' ? 'selected' : '' }}>PHP mail</option>
              <option value="smtp" {{ $method === 'smtp' ? 'selected' : '' }}>SMTP</option>
              <option value="sendgrid" {{ $method === 'sendgrid' ? 'selected' : '' }}>SendGrid API</option>
              <option value="mailjet" {{ $method === 'mailjet' ? 'selected' : '' }}>Mailjet API</option>
            </select>
            <span class="dash-hint" style="display:block;margin-top:4px;font-size:0.8rem;color:var(--d-text-secondary);">For most setups, choose SMTP and enter your mail host, port, and credentials.</span>
          </div>
          <div id="email-method-smtp" style="{{ $method === 'smtp' ? '' : 'display:none;' }}">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;">
              <div>
                <label for="host" class="dash-label">SMTP host</label>
                <input type="text" id="host" name="host" class="dash-input" value="{{ old('host', $mailConfig->host ?? '') }}" placeholder="smtp.yourmail.com">
                @error('host') <span class="dash-error">{{ $message }}</span> @enderror
              </div>
              <div>
                <label for="port" class="dash-label">Port</label>
                <input type="text" id="port" name="port" class="dash-input" value="{{ old('port', $mailConfig->port ?? '') }}" placeholder="587">
                @error('port') <span class="dash-error">{{ $message }}</span> @enderror
              </div>
              <div>
                <label for="enc" class="dash-label">Encryption</label>
                @php $enc = old('enc', $mailConfig->enc ?? 'tls'); @endphp
                <select id="enc" name="enc" class="dash-input">
                  <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                  <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                </select>
                @error('enc') <span class="dash-error">{{ $message }}</span> @enderror
              </div>
              <div>
                <label for="username" class="dash-label">Username</label>
                <input type="text" id="username" name="username" class="dash-input" value="{{ old('username', $mailConfig->username ?? '') }}" placeholder="Your SMTP username">
                @error('username') <span class="dash-error">{{ $message }}</span> @enderror
              </div>
              <div>
                <label for="password" class="dash-label">Password</label>
                <input type="password" id="password" name="password" class="dash-input" value="{{ old('password', $mailConfig->password ?? '') }}" placeholder="Your SMTP password">
                @error('password') <span class="dash-error">{{ $message }}</span> @enderror
              </div>
            </div>
          </div>
        </div>

        <hr style="border:none;border-top:1px solid var(--d-border,#2a2e3d);margin:8px 0;">

        <div>
          <h2 style="font-size:1rem;margin:0 0 8px 0;">Global email template</h2>
          <p class="dash-hint" style="margin-bottom:12px;">This HTML wrapper is used for all emails. Use <code>{{ '{{fullname}}' }}</code>, <code>{{ '{{username}}' }}</code> and <code>{{ '{{message}}' }}</code> placeholders.</p>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
            <div>
              <label for="email_from_name" class="dash-label">Email sent from – name</label>
              <input type="text" id="email_from_name" name="email_from_name" class="dash-input" value="{{ old('email_from_name', $emailFromName) }}" placeholder="Eden">
              @error('email_from_name') <span class="dash-error">{{ $message }}</span> @enderror
            </div>
            <div>
              <label for="email_from" class="dash-label">Email sent from – email</label>
              <input type="email" id="email_from" name="email_from" class="dash-input" value="{{ old('email_from', $emailFrom) }}" placeholder="no-reply@example.com">
              @error('email_from') <span class="dash-error">{{ $message }}</span> @enderror
            </div>
          </div>
          <div style="margin-top:14px;">
            <label for="email_template" class="dash-label">HTML template</label>
            <textarea id="email_template" name="email_template" rows="10" class="dash-input" style="font-family: ui-monospace, monospace; font-size: 0.85rem;" placeholder="&lt;html&gt;...&lt;/html&gt;">{{ old('email_template', $emailTemplate ?: '<!DOCTYPE html>
<html>
  <head>
    <meta charset=\"UTF-8\">
    <title>{{ '{{subject}}' }}</title>
    <style>
      body { font-family: system-ui, -apple-system, BlinkMacSystemFont, \"Segoe UI\", sans-serif; background: #0b1020; color: #e5e7eb; margin:0; padding:24px; }
      .wrapper { max-width: 560px; margin: 0 auto; background: #111827; border-radius: 12px; padding: 24px; border: 1px solid #1f2937; }
      .brand { font-weight: 600; font-size: 1.1rem; margin-bottom: 12px; }
      .content { font-size: 0.95rem; line-height: 1.6; }
      .footer { margin-top: 24px; font-size: 0.75rem; color: #9ca3af; }
    </style>
  </head>
  <body>
    <div class=\"wrapper\">
      <div class=\"brand\">Eden</div>
      <div class=\"content\">
        {{ '{{message}}' }}
      </div>
      <div class=\"footer\">
        You are receiving this because you have an account on Eden.
      </div>
    </div>
  </body>
</html>') }}</textarea>
            @error('email_template') <span class="dash-error">{{ $message }}</span> @enderror
          </div>
        </div>

        <hr style="border:none;border-top:1px solid var(--d-border,#2a2e3d);margin:8px 0;">

        <div>
          <h2 style="font-size:1rem;margin:0 0 8px 0;">Welcome & verification emails</h2>
          <p class="dash-hint" style="margin-bottom:12px;">Control whether new users get a welcome email, whether email verification is required, and customize both templates.</p>
          <div style="display:flex;flex-direction:column;gap:16px;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
              <input type="checkbox" name="email_notifications_enabled" value="1" {{ old('email_notifications_enabled', $emailNotificationsEnabled) ? 'checked' : '' }}>
              <span class="dash-label" style="margin-bottom:0;">Enable email notifications</span>
            </label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
              <input type="checkbox" name="welcome_email_enabled" value="1" {{ old('welcome_email_enabled', $welcomeEmailEnabled) ? 'checked' : '' }}>
              <span class="dash-label" style="margin-bottom:0;">Send welcome email on signup</span>
            </label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
              <input type="checkbox" name="verification_required" value="1" {{ old('verification_required', $verificationRequired) ? 'checked' : '' }}>
              <span class="dash-label" style="margin-bottom:0;">Require email verification for new accounts</span>
            </label>
          </div>

          <div style="margin-top:18px;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;">
            <div>
              <h3 style="font-size:0.95rem;margin-bottom:8px;">Welcome email template</h3>
              @php
                $welcomeSubject = old('welcome_subject', optional($welcomeTemplate)->subject ?? 'Welcome to Eden');
                $welcomeBody = old('welcome_body', optional($welcomeTemplate)->email_body ?? '<p>Hi {{fullname}},</p><p>Welcome to Eden! Your account has been created successfully.</p>');
              @endphp
              <div style="display:flex;flex-direction:column;gap:10px;">
                <div>
                  <label for="welcome_subject" class="dash-label">Subject</label>
                  <input type="text" id="welcome_subject" name="welcome_subject" class="dash-input" value="{{ $welcomeSubject }}">
                  @error('welcome_subject') <span class="dash-error">{{ $message }}</span> @enderror
                </div>
                <div>
                  <label for="welcome_body" class="dash-label">HTML body</label>
                  <textarea id="welcome_body" name="welcome_body" rows="8" class="dash-input" style="font-family: ui-monospace, monospace; font-size: 0.85rem;">{{ $welcomeBody }}</textarea>
                  @error('welcome_body') <span class="dash-error">{{ $message }}</span> @enderror
                </div>
              </div>
            </div>
            <div>
              <h3 style="font-size:0.95rem;margin-bottom:8px;">Verification email template</h3>
              @php
                $verificationSubject = old('verification_subject', optional($verificationTemplate)->subject ?? 'Verify your email address');
                $verificationBody = old('verification_body', optional($verificationTemplate)->email_body ?? '<p>Hi {{fullname}},</p><p>Your verification code is <strong>{{code}}</strong>.</p>');
              @endphp
              <div style="display:flex;flex-direction:column;gap:10px;">
                <div>
                  <label for="verification_subject" class="dash-label">Subject</label>
                  <input type="text" id="verification_subject" name="verification_subject" class="dash-input" value="{{ $verificationSubject }}">
                  @error('verification_subject') <span class="dash-error">{{ $message }}</span> @enderror
                </div>
                <div>
                  <label for="verification_body" class="dash-label">HTML body</label>
                  <textarea id="verification_body" name="verification_body" rows="8" class="dash-input" style="font-family: ui-monospace, monospace; font-size: 0.85rem;">{{ $verificationBody }}</textarea>
                  @error('verification_body') <span class="dash-error">{{ $message }}</span> @enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <button type="submit" class="dash-btn dash-btn-primary">
            <i class="fa-solid fa-check"></i> Save email settings
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  (function() {
    var methodSelect = document.getElementById('email_method');
    var smtpBlock = document.getElementById('email-method-smtp');
    if (methodSelect && smtpBlock) {
      methodSelect.addEventListener('change', function () {
        if (this.value === 'smtp') {
          smtpBlock.style.display = '';
        } else {
          smtpBlock.style.display = 'none';
        }
      });
    }
  })();
</script>

