<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install Eden</title>
    <link rel="stylesheet" href="{{ asset('css/install.css') }}">
</head>
<body>
    <h1>Install Eden</h1>
    @if(!$allOk)
        <p>Please fix the following requirements:</p>
        @foreach($requirements as $name => $ok)
            <div class="req {{ $ok ? 'ok' : 'fail' }}">{{ $name }}: {{ $ok ? 'OK' : 'Missing' }}</div>
        @endforeach
    @else
        <form method="POST" action="{{ route('install.store') }}">
            @csrf
            <div class="form-group">
                <label>Site name</label>
                <input type="text" name="site_name" value="{{ old('site_name', 'Eden') }}" required>
                @error('site_name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>App URL</label>
                <input type="url" name="app_url" value="{{ old('app_url', url('/')) }}" required>
                @error('app_url')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Database host</label>
                <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required>
            </div>
            <div class="form-group">
                <label>Database name</label>
                <input type="text" name="db_database" value="{{ old('db_database', 'eden') }}" required>
            </div>
            <div class="form-group">
                <label>Database username</label>
                <input type="text" name="db_username" value="{{ old('db_username', 'root') }}" required>
            </div>
            <div class="form-group">
                <label>Database password</label>
                <input type="password" name="db_password" value="{{ old('db_password') }}">
            </div>
            <div class="form-group">
                <label>Timezone</label>
                <input type="text" name="timezone" value="{{ old('timezone', 'UTC') }}" required>
            </div>
            <div class="form-group">
                <label>Logo URL (optional)</label>
                <input type="url" name="site_logo_url" value="{{ old('site_logo_url') }}" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>Admin name</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required>
            </div>
            <div class="form-group">
                <label>Admin email</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" required>
            </div>
            <div class="form-group">
                <label>Admin password</label>
                <input type="password" name="admin_password" required>
            </div>
            <div class="form-group">
                <label>Confirm password</label>
                <input type="password" name="admin_password_confirmation" required>
            </div>
            @error('db')<div class="error">{{ $message }}</div>@enderror
            <button type="submit">Install</button>
        </form>
    @endif
</body>
</html>
