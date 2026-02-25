@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $resetContent = getContent('reset_section.content', true);
    @endphp

    <div class="section ">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="login-section__content">
                        <div class="row justify-content-center">
                            <div class="col-lg-6 d-none d-lg-block">
                                <div class="text-center login-section__image">
                                    <img alt="image" class="img-fluid login-section__image-is" src="{{frontendImage('reset_section' , @$resetContent->data_values->image, '425x600') }}">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-8 col-sm-10">
                                <div class="login-form">
                                    <div class="mb-4">
                                        <p>{{ __($resetContent->data_values->title) }}</p>
                                    </div>
                                    <form action="{{ route('user.password.email') }}" class="verify-gcaptcha" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label class="form-label">@lang('Email or Username')</label>
                                            <input autofocus="off" class="form-control form--control" name="value" required type="text" value="{{ old('value') }}">
                                        </div>

                                        <x-captcha />

                                        <div class="form-group">
                                            <button class="btn btn--base  h-45  w-100" type="submit">@lang('Submit')</button>
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
