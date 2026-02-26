@extends('admin.layouts.master')
@section('content')
<div class="admin-login">
    <div class="admin-login__bg"></div>
    <div class="admin-login__container">
        <div class="admin-login__card">
            <div class="admin-login__header">
                <div class="admin-login__logo">
                    <span class="admin-login__logo-dots"><span></span><span></span><span></span><span></span></span>
                    {{ __(gs('site_name') ?? 'Eden') }}
                </div>
                <h1 class="admin-login__title">Admin Login</h1>
                <p class="admin-login__subtitle">Sign in to {{ __(gs('site_name') ?? 'Eden') }} Dashboard</p>
            </div>
            <div class="admin-login__body">
                <form action="{{ route('admin.login') }}" method="POST" class="admin-login__form verify-gcaptcha">
                    @csrf
                    <div class="admin-login__field">
                        <label for="admin-username" class="admin-login__label">Username</label>
                        <input type="text" id="admin-username" class="admin-login__input" name="username" value="{{ old('username') }}" placeholder="Enter your username" required autofocus>
                    </div>
                    <div class="admin-login__field">
                        <div class="admin-login__label-row">
                            <label for="admin-password" class="admin-login__label">Password</label>
                            <a href="{{ route('admin.password.reset') }}" class="admin-login__forgot">Forgot password?</a>
                        </div>
                        <input type="password" id="admin-password" class="admin-login__input" name="password" placeholder="Enter your password" required>
                    </div>
                    <x-captcha />
                    <button type="submit" class="admin-login__btn">Log in</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
