@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $verifyContent = getContent('verify_code.content', true);
    @endphp
    <div class="section bg--light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="login-section__content">
                        <div class="row justify-content-center">
                            <div class="col-lg-6 d-none d-lg-block">
                                <div class="text-center login-section__image">
                                    <img src="{{ frontendImage('verify_code', @$verifyContent->data_values->image, '425x600') }}" alt="image"
                                        class="img-fluid login-section__image-is">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-center">
                                    <div class="verification-code-wrapper login-form ">
                                        <div class="verification-area">
                                            <form action="{{ route('user.password.verify.code') }}" method="POST" class="submit-form">
                                                @csrf
                                                <p class="verification-text mb-3">{{ __($verifyContent->data_values->title) }} :
                                                    {{ showEmailAddress($email) }}</p>
                                                <input type="hidden" name="email" value="{{ $email }}">

                                                @include($activeTemplate . 'partials.verification_code')

                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--base h-45 w-100">@lang('Submit')</button>
                                                </div>

                                                <div class="form-group">
                                                    @lang('Please check including your Junk/Spam Folder. if not found, you can')
                                                    <a href="{{ route('user.password.request') }}" class="text--base">@lang('Try to send again')</a>
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
        </div>
    </div>
@endsection
