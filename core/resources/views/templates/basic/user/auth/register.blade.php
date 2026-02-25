@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $register = getContent('register.content', true);
        $policyPages = getContent('policy_pages.element', orderById: true);
    @endphp
    @if (gs('registration'))
        <div class="section ">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="login-section__content">
                            <div class="row justify-content-center">
                                <div class="col-xl-6 col-lg-5 d-none d-lg-block">
                                    <div class="text-center login-section__image">
                                        <img alt="image" class="img-fluid login-section__image-is"
                                            src="{{ frontendImage('register', @$register->data_values->image, '425x600') }}">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-7 col-md-10 ">
                                    <div class="login-form">
                                        <div class="login-form__head mb-4">
                                            <h4 class="mt-lg-0 text-center">
                                                {{ __(@$register->data_values->heading) }}</h4>
                                            <p class="text-center section__para mx-auto">
                                                {{ __(@$register->data_values->subheading) }}
                                            </p>
                                        </div>
                                        @include($activeTemplate . 'partials.social_login')

                                        <form action="{{ route('user.register') }}" method="POST" class="verify-gcaptcha disableSubmission">
                                            @csrf
                                            <div class="row">
                                                @if (session()->get('reference') != null)
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="referenceBy" class="form-label">@lang('Reference By')</label>
                                                            <input type="text" name="referBy" id="referenceBy" class="form-control form--control"
                                                                value="{{ session()->get('reference') }}" readonly>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('Full Name')</label>
                                                        <input type="text" class="form-control form--control" name="fullname"
                                                            value="{{ old('fullname') }}" required 
                                                            placeholder="@lang('Enter your full name')"
                                                            minlength="3" maxlength="100">
                                                        <small class="text-muted">@lang('Please enter your full legal name')</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('E-Mail Address')</label>
                                                        <input type="email" class="form-control form--control checkUser" name="email"
                                                            value="{{ old('email',@request()->invite_email) }}" required @readonly(@request()->invite_email)
                                                            placeholder="@lang('Enter your email address')">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('Phone Number')</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text mobile-code">+{{ $detectedMobileCode ?? '1' }}</span>
                                                            <input type="hidden" name="mobile_code" value="{{ old('mobile_code', $detectedMobileCode ?? '1') }}">
                                                            <input type="hidden" name="country_code" value="{{ old('country_code', $detectedCountryCode ?? 'US') }}">
                                                            <input type="tel" name="mobile" value="{{ old('mobile') }}"
                                                                class="form-control form--control" required
                                                                placeholder="@lang('Enter phone number')"
                                                                pattern="[0-9]*" inputmode="numeric">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group select2-parent">
                                                        <label class="form-label">@lang('Country')</label>
                                                        <select name="country" class="form-control form--control select2-basic" required>
                                                            @php
                                                                $countryData = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
                                                                $selectedCountry = old('country', $detectedCountry);
                                                            @endphp
                                                            @foreach ($countryData as $key => $country)
                                                                <option value="{{ $country->country }}" 
                                                                    data-code="{{ $key }}" 
                                                                    data-mobile_code="{{ $country->dial_code }}"
                                                                    {{ $selectedCountry == $country->country ? 'selected' : '' }}>
                                                                    {{ __($country->country) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('Password')</label>
                                                        <input type="password"
                                                            class="form-control form--control @if (gs('secure_password')) secure-password @endif"
                                                            name="password" required
                                                            placeholder="@lang('Enter password')">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('Confirm Password')</label>
                                                        <input type="password" class="form-control form--control" name="password_confirmation" required
                                                            placeholder="@lang('Confirm password')">
                                                    </div>
                                                </div>

                                                <x-captcha />

                                            </div>

                                            @if (gs('agree'))
                                                @php
                                                    $policyPages = getContent('policy_pages.element', false, orderById: true);
                                                @endphp
                                                <div class="form-group">
                                                    <input type="checkbox" id="agree" @checked(old('agree')) name="agree" required>
                                                    <label for="agree">@lang('I agree with')</label> <span>
                                                        @foreach ($policyPages as $policy)
                                                            <a href="{{ route('policy.pages', $policy->slug) }}"
                                                                target="_blank">{{ __($policy->data_values->title) }}</a>
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="form-group">
                                                <button type="submit" id="recaptcha" class="btn btn--base h-45 w-100">
                                                    @lang('Register')</button>
                                            </div>
                                            <p class="mb-0">@lang('Already have an account?') <a href="{{ route('user.login') }}">@lang('Login')</a></p>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include($activeTemplate . 'partials.registration_disabled')

    @endif


    <div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-labelledby="existModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h6 class="text-center">@lang('You already have an account please Login ')</h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn-sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .social-login-btn {
            border: 1px solid #cbc4c4;
        }
    </style>
@endpush

@push('style-lib')
<link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
<script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@if (gs('secure_password'))
    <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
@endif
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {
            // Select2 is already initialized globally in app.blade.php
            // No need to initialize again to avoid duplicate dropdowns
            
            // Update mobile code and country code when country changes
            $('select[name=country]').on('change', function() {
                var selectedOption = $(this).find(':selected');
                var mobileCode = selectedOption.data('mobile_code');
                var countryCode = selectedOption.data('code');
                
                $('input[name=mobile_code]').val(mobileCode);
                $('input[name=country_code]').val(countryCode);
                $('.mobile-code').text('+' + mobileCode);
            });

            // Set initial mobile code
            var initialOption = $('select[name=country]').find(':selected');
            if (initialOption.length) {
                $('input[name=mobile_code]').val(initialOption.data('mobile_code'));
                $('input[name=country_code]').val(initialOption.data('code'));
                $('.mobile-code').text('+' + initialOption.data('mobile_code'));
            }

            // Check if email exists
            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                var data = {
                    email: value,
                    _token: token
                }

                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            });

            // Validate full name on input
            $('input[name="fullname"]').on('input', function() {
                var fullname = $(this).val().trim();
                if (fullname.length > 0 && fullname.length < 3) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
        })(jQuery);
    </script>
@endpush
