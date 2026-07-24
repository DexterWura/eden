@extends('admin.layouts.master')
@section('content')
<main class="admin-login">
    <div class="admin-login__bg"></div>
    <div class="admin-login__container">
        <div class="admin-login__card">
            <div class="admin-login__header">
                <div class="admin-login__logo"><span class="admin-login__logo-dots"><span></span><span></span><span></span><span></span></span>{{ __(gs('site_name') ?? 'Eden') }}</div>
                <h1 class="admin-login__title">Verify reset code</h1>
                <p class="admin-login__subtitle">If an eligible account matched, the six-digit code was sent by email and expires in 15 minutes.</p>
            </div>
            <div class="admin-login__body">
                <form action="{{ route('admin.password.verify.code') }}" method="POST" class="admin-login__form">
                    @csrf
                    <div class="admin-login__field">
                        <label for="reset-code" class="admin-login__label">Verification code</label>
                        <input type="text" id="reset-code" class="admin-login__input" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                    </div>
                    <button type="submit" class="admin-login__btn">Verify code</button>
                    <div class="admin-login__label-row">
                        <a href="{{ route('admin.password.reset') }}" class="admin-login__forgot">Send another code</a>
                        <a href="{{ route('admin.login') }}" class="admin-login__forgot">Back to login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
