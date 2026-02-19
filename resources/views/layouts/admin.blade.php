<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="admin-header-inner">
            <button type="button" class="admin-menu-btn" id="admin-menu-btn" aria-label="Toggle menu">
                <span class="admin-menu-icon"></span><span class="admin-menu-icon"></span><span class="admin-menu-icon"></span>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <span class="admin-brand-icon">E</span>
                <span class="admin-brand-text">Eden Admin</span>
            </a>
            <div class="admin-header-spacer"></div>
            <div class="admin-header-actions">
                <a href="{{ route('startups.index') }}" class="admin-header-link">View site</a>
                <div class="admin-user">
                    <span class="admin-user-name">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="admin-logout-wrap">
                        @csrf
                        <button type="submit" class="admin-logout-btn">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    <aside class="admin-sidebar" id="admin-sidebar">
        <nav class="admin-nav">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="admin-nav-icon">◉</span>
                    <span>Reports snapshot</span>
                </a>
            @endif
            <a href="{{ route('admin.startups.index') }}" class="admin-nav-item {{ request()->routeIs('admin.startups.*') ? 'active' : '' }}">
                <span class="admin-nav-icon">▤</span>
                <span>Startups</span>
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.submissions.index') }}" class="admin-nav-item {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">⊕</span>
                    <span>Submissions</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="admin-nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">☰</span>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.ads.index') }}" class="admin-nav-item {{ request()->routeIs('admin.ads.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">▢</span>
                    <span>Ads</span>
                </a>
                <div class="admin-nav-section">Configure</div>
                <a href="{{ route('admin.settings.index') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">⚙</span>
                    <span>Settings</span>
                </a>
                <a href="{{ route('admin.migrations.index') }}" class="admin-nav-item {{ request()->routeIs('admin.migrations.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">↻</span>
                    <span>Migrations</span>
                </a>
                <a href="{{ route('admin.health.index') }}" class="admin-nav-item {{ request()->routeIs('admin.health.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">❤</span>
                    <span>System Health</span>
                </a>
                <a href="{{ route('admin.pruning.index') }}" class="admin-nav-item {{ request()->routeIs('admin.pruning.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">✂</span>
                    <span>Pruning</span>
                </a>
            @endif
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-content">
            @if(session('success'))<div class="admin-alert admin-alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="admin-alert admin-alert-error">{{ session('error') }}</div>@endif
            @yield('content')
        </div>
    </main>
    <script>
        document.getElementById('admin-menu-btn')?.addEventListener('click', function() {
            document.getElementById('admin-sidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
