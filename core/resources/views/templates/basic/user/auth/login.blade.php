@extends($activeTemplate . 'layouts.frontend')

@section('content')
    @php
        $loginContent = getContent('login.content', true);
    @endphp
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="login-section__content">
                        <div class="row  justify-content-center">
                            <div class="col-lg-6 d-none d-lg-block">
                                <div class="text-center login-section__image">
                                    <img src="{{ frontendImage('login', @$loginContent->data_values->image, '425x600') }}" alt="image"
                                        class="img-fluid login-section__image-is">
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-10">
                                <div class="login-form">
                                    <div class="login-form__head mb-4">
                                        <h4 class="mt-lg-0 text-center">
                                            {{ __(@$loginContent->data_values->heading) }}
                                        </h4>
                                        <p class="text-center section__para mx-auto">
                                            {{ __(@$loginContent->data_values->subheading) }}
                                        </p>
                                    </div>

                                    @include($activeTemplate . 'partials.social_login')

                                    <form method="POST" action="{{ route('user.login') }}" class="verify-gcaptcha">
                                        @csrf

                                        <div class="form-group ">
                                            <label class="form-label">@lang('Username or Email')</label>
                                            <input type="text" name="username" value="{{ old('username') }}" class="form-control form--control"
                                                required>
                                        </div>

                                        <div class="form-group ">
                                            <label class="form-label">@lang('Password')</label>
                                            <input type="password" class="form-control form--control" name="password" required>
                                        </div>

                                        <div class="mb-3 text-end d-flex justify-content-between flex-wrap gap-2">
                                            <div>
                                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remember">
                                                    @lang('Remember Me')
                                                </label>
                                            </div>
                                            <a class="forgot-pass text--base text-end" href="{{ route('user.password.request') }}">
                                                @lang('Forgot Password?')
                                            </a>
                                        </div>
                                        <x-captcha />
                                        <div class="form-group">
                                            <button type="submit" id="recaptcha" class="btn btn--base w-100 h-45">
                                                @lang('Login')
                                            </button>
                                        </div>
                                        <p class="mb-0">@lang('Don\'t have any account?') <a href="{{ route('user.register') }}"
                                                class="text--base">@lang('Register')</a>
                                        </p>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
