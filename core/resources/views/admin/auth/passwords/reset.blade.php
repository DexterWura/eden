@extends('admin.layouts.master')
@section('content')
<main class="admin-login">
    <div class="admin-login__bg"></div>
    <div class="admin-login__container">
        <div class="admin-login__card">
            <div class="admin-login__header">
                <div class="admin-login__logo"><span class="admin-login__logo-dots"><span></span><span></span><span></span><span></span></span>{{ __(gs('site_name') ?? 'Eden') }}</div>
                <h1 class="admin-login__title">Choose a new password</h1>
                <p class="admin-login__subtitle">Use at least 12 characters and avoid reusing another account’s password.</p>
            </div>
            <div class="admin-login__body">
                <form action="{{ route('admin.password.change') }}" method="POST" class="admin-login__form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="admin-login__field">
                        <label for="new-password" class="admin-login__label">New password</label>
                        <input type="password" id="new-password" class="admin-login__input" name="password" minlength="12" autocomplete="new-password" required autofocus>
                    </div>
                    <div class="admin-login__field">
                        <label for="password-confirmation" class="admin-login__label">Confirm new password</label>
                        <input type="password" id="password-confirmation" class="admin-login__input" name="password_confirmation" minlength="12" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="admin-login__btn">Reset password</button>
                    <a href="{{ route('admin.login') }}" class="admin-login__forgot">Back to admin login</a>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
