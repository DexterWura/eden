@extends($activeTemplate . 'layouts.frontend')
@section('content')
@php
$verifyContent = getContent('verify_section.content', true);
@endphp

<div class="section bg--light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="login-section__content">
                    <div class="row justify-content-center align-items-center">
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="text-center login-section__image">
                                <img src="{{ frontendImage('verify_section' , @$verifyContent->data_values->image, '425x600') }}"
                                    alt="image" class="img-fluid login-section__image-is">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="verification-code-wrapper">
                                <div class="verification-area">
                                    <form action="{{route('user.2fa.verify')}}" method="POST" class="submit-form">
                                        @csrf

                                        @include($activeTemplate.'partials.verification_code')

                                        <div class="form--group">
                                            <button type="submit" class="btn btn--base h-45 w-100">@lang('Submit')</button>
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
@endsection