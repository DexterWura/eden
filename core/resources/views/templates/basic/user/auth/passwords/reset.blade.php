@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $authContent = getContent('auth.content', true);
    @endphp
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="login-section__content">
                        <div class="row">
                            <div class="col-lg-6  d-none d-lg-block">
                                <div class="text-center login-section__image">
                                    <img alt="image" class="img-fluid login-section__image-is"
                                        src="{{ frontendImage('auth', @$authContent->data_values->image, '425x600') }}">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <div class="login-form">
                                    <div class="mb-4">
                                        <p>@lang('Your account is verified successfully. Now you can change your password. Please enter a strong password and don\'t share it with anyone.')</p>
                                    </div>
                                    <form action="{{ route('user.password.update') }}" method="POST">
                                        @csrf
                                        <input name="email" type="hidden" value="{{ $email }}">
                                        <input name="token" type="hidden" value="{{ $token }}">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Password')</label>
                                            <input class="form-control form--control @if (gs('secure_password')) secure-password @endif"
                                                name="password" required type="password">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">@lang('Confirm Password')</label>
                                            <input class="form-control form--control" name="password_confirmation" required type="password">
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn--base  h-45  w-100" type="submit">
                                                @lang('Submit')</button>
                                        </div>
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

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
