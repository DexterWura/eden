@extends('admin.layouts.master')
@section('content')
<main class="admin-login">
    <div class="admin-login__bg"></div>
    <div class="admin-login__container">
        <div class="admin-login__card">
            <div class="admin-login__header">
                <div class="admin-login__logo"><span class="admin-login__logo-dots"><span></span><span></span><span></span><span></span></span>{{ __(gs('site_name') ?? 'Eden') }}</div>
                <h1 class="admin-login__title">Reset admin password</h1>
                <p class="admin-login__subtitle">Enter your admin email. We’ll send a short-lived verification code if the account is eligible.</p>
            </div>
            <div class="admin-login__body">
                <form action="{{ route('admin.password.reset') }}" method="POST" class="admin-login__form verify-gcaptcha">
                    @csrf
                    <div class="admin-login__field">
                        <label for="admin-email" class="admin-login__label">Email</label>
                        <input type="email" id="admin-email" class="admin-login__input" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    </div>
                    <x-captcha />
                    <button type="submit" class="admin-login__btn">Send verification code</button>
                    <a href="{{ route('admin.login') }}" class="admin-login__forgot">Back to admin login</a>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
