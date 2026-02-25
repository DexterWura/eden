@extends('layouts.admin')

@section('title', 'System Health')

@section('content')
    <h1>System Health</h1>
    <p>Last health check: {{ $lastHealthCheck ?? 'Never' }}</p>
    <p>Last cleanup: {{ $lastCleanup ?? 'Never' }}</p>
    <p>Last reminder: {{ $lastReminder ?? 'Never' }}</p>
    <p>Last newsletter: {{ $lastNewsletter ?? 'Never' }}</p>
    <p>Run commands manually:</p>
    <form method="POST" action="{{ route('admin.health.run', 'health-check') }}" class="form-inline">@csrf<button type="submit" class="btn">Run health check</button></form>
    <form method="POST" action="{{ route('admin.health.run', 'cleanup') }}" class="form-inline">@csrf<button type="submit" class="btn">Run cleanup</button></form>
    <form method="POST" action="{{ route('admin.health.run', 'remind-updates') }}" class="form-inline">@csrf<button type="submit" class="btn">Run reminder</button></form>
    <form method="POST" action="{{ route('admin.health.run', 'newsletter') }}" class="form-inline">@csrf<button type="submit" class="btn">Run newsletter</button></form>
    <p class="cron-note">On shared hosting, add a cron job: <code>* * * * * php /path/to/artisan schedule:run</code></p>
@endsection
