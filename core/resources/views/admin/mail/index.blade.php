@extends('layouts.admin')

@section('title', 'Mail')

@section('content')
    <h1>Mail</h1>
    <p class="page-sub">Choose how the application sends email: SMTP or PHP mail (sendmail).</p>

    <form method="POST" action="{{ route('admin.mail.update') }}" class="form-max-600">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Mail driver *</label>
            <select name="mail_driver" id="mail_driver" required>
                <option value="default" {{ old('mail_driver', $mail_driver) === 'default' ? 'selected' : '' }}>Use server default (.env)</option>
                <option value="smtp" {{ old('mail_driver', $mail_driver) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                <option value="php" {{ old('mail_driver', $mail_driver) === 'php' ? 'selected' : '' }}>PHP mail (sendmail)</option>
            </select>
            <small class="form-hint">Use SMTP for an external server (Gmail, Mailgun, etc.). Use PHP mail when your server sends mail via sendmail/php mail().</small>
        </div>

        <div id="smtp-fields" class="{{ old('mail_driver', $mail_driver) === 'smtp' ? '' : 'hidden' }}">
            <h2 class="section-title-sm">SMTP settings</h2>
            <div class="form-group">
                <label>Host</label>
                <input type="text" name="mail_host" value="{{ old('mail_host', $mail_host) }}" placeholder="smtp.example.com">
            </div>
            <div class="form-group">
                <label>Port</label>
                <input type="number" name="mail_port" value="{{ old('mail_port', $mail_port) }}" placeholder="587" min="1" max="65535">
            </div>
            <div class="form-group">
                <label>Encryption</label>
                <select name="mail_encryption">
                    <option value="">None</option>
                    <option value="tls" {{ old('mail_encryption', $mail_encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ old('mail_encryption', $mail_encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                </select>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="mail_username" value="{{ old('mail_username', $mail_username) }}" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="mail_password" value="{{ old('mail_password', $mail_password) }}" placeholder="Leave blank to keep current" autocomplete="new-password">
                <small class="form-hint">Leave blank to keep the existing password.</small>
            </div>
        </div>

        <h2 class="section-title-sm">From address (all outgoing mail)</h2>
        <div class="form-group">
            <label>From email *</label>
            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $mail_from_address) }}" required placeholder="noreply@yoursite.com">
        </div>
        <div class="form-group">
            <label>From name *</label>
            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $mail_from_name) }}" required placeholder="{{ config('app.name') }}">
        </div>

        <button type="submit" class="btn">Save</button>
    </form>

    <script>
        document.getElementById('mail_driver').addEventListener('change', function () {
            document.getElementById('smtp-fields').classList.toggle('hidden', this.value !== 'smtp');
        });
    </script>
@endsection
