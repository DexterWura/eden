<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', app(\App\Services\SettingService::class)->get('site_name', config('app.name')))</title>
    @stack('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        $themes = config('themes.themes', ['basic' => 'Basic']);
        $theme = app(\App\Services\SettingService::class)->get('theme', config('themes.default', 'basic'));
        $theme = isset($themes[$theme]) ? $theme : array_key_first($themes);
    @endphp
    <link rel="stylesheet" href="{{ asset('css/themes/' . $theme . '.css') }}">
    @stack('styles')
</head>
<body>
    <header>
        <div class="container">
            <a href="{{ route('home') }}" class="logo">
                @php $logo = app(\App\Services\SettingService::class)->get('site_logo'); @endphp
                @if($logo)<img src="{{ $logo }}" alt="">@else{{ app(\App\Services\SettingService::class)->get('site_name', config('app.name')) }}@endif
            </a>
            <nav>
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('startups.index') }}">Startups</a>
                <a href="{{ route('startups.create') }}">Submit startup</a>
                <a href="{{ route('blog.index') }}">Blog</a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @else
                        <a href="{{ route('my.startups.index') }}">My startups</a>
                    @endif
                    @if(auth()->user()->hasBloggingAccess())
                        <a href="{{ route('my.blog.index') }}">My blog</a>
                    @else
                        <a href="{{ route('pro.index') }}">Upgrade</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="form-inline">
                        @csrf
                        <button type="submit" class="btn btn-gold btn-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
    </header>
    @if(session('success'))<div class="container"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
    @if(session('error'))<div class="container"><div class="alert alert-error">{{ session('error') }}</div></div>@endif
    @if(session('info'))<div class="container"><div class="alert alert-info">{{ session('info') }}</div></div>@endif
    @if($errors->any())<div class="container"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif
    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>
    <footer>
        <div class="container">
            <p>{{ app(\App\Services\SettingService::class)->get('site_name', config('app.name')) }} &copy; {{ date('Y') }}</p>
            <form method="POST" action="{{ route('newsletter.store') }}" class="footer-newsletter">
                @csrf
                <input type="email" name="email" placeholder="Email for newsletter" required>
                <button type="submit" class="btn">Subscribe</button>
            </form>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
