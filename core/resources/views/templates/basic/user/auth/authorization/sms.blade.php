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
                        <div class="row justify-content-center">
                            <div class="col-lg-6  d-none d-lg-block">
                                <div class="text-center login-section__image">
                                    <img src="{{ frontendImage('verify_section', @$verifyContent->data_values->image, '425x600') }}" alt="image"
                                        class="img-fluid login-section__image-is">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-center">
                                    <div class="verification-code-wrapper login-form">
                                        <div class="verification-area">
                                            <form action="{{ route('user.verify.mobile') }}" method="POST" class="submit-form">
                                                @csrf
                                                <p class="verification-text mb-3">@lang('A 6 digit verification code sent to your mobile number') : +{{ showMobileNumber(auth()->user()->mobileNumber) }}</p>
                                                @include($activeTemplate . 'partials.verification_code')
                                                <div class="mb-3">
                                                    <button type="submit" class="btn btn--base h-45 w-100">@lang('Submit')</button>
                                                </div>
                                                <div class="form-group">
                                                    <p>
                                                        @lang('If you don\'t get any code'), <span class="countdown-wrapper">@lang('try again after') <span id="countdown"
                                                                class="fw-bold">--</span> @lang('seconds')</span>
                                                        <a href="{{ route('user.send.verify.code', 'sms') }}" class="try-again-link d-none">
                                                            @lang('Try again')</a>
                                                    </p>
                                                    <a href="{{ route('user.logout') }}">@lang('Logout')</a>
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
@push('script')
    <script>
        var distance = Number("{{ @$user->ver_code_send_at->addMinutes(2)->timestamp - time() }}");
        var x = setInterval(function() {
            distance--;
            document.getElementById("countdown").innerHTML = distance;
            if (distance <= 0) {
                clearInterval(x);
                document.querySelector('.countdown-wrapper').classList.add('d-none');
                document.querySelector('.try-again-link').classList.remove('d-none');
            }
        }, 1000);
    </script>
@endpush
