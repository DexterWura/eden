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
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="text-center login-section__image">
                                <img src="{{ frontendImage('auth' , @$authContent->data_values->image, '425x600') }}"
                                    alt="@lang('image')" class="img-fluid login-section__image-is">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="login-form">
                                @if(session('social_signup_provider'))
                                    @php
                                        $providerName = match(session('social_signup_provider')) {
                                            'linkedin' => 'LinkedIn',
                                            'google' => 'Google',
                                            default => ucfirst(session('social_signup_provider')),
                                        };
                                    @endphp
                                    <div class="alert alert-info mb-4" role="alert">
                                        <h5 class="alert-heading"><i class="las la-info-circle"></i> @lang('general.social_signup_heading', ['provider' => $providerName])</h5>
                                        <p class="mb-0">@lang('general.social_signup_message', ['provider' => $providerName])</p>
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('user.data.submit') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label">@lang('Username')</label>
                                                <input type="text" class="form-control form--control checkUser"
                                                    name="username" value="{{ old('username') }}">
                                                <small class="text--danger usernameExist"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group select2-parent">
                                                <label class="form-label">@lang('Country')</label>
                                                <select name="country" class="form-control form--control select2-basic"
                                                    required>
                                                    @foreach ($countries as $key => $country)
                                                    <option data-mobile_code="{{ $country->dial_code }}"
                                                        value="{{ $country->country }}" data-code="{{ $key }}">{{
                                                        __($country->country) }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">@lang('Mobile')</label>
                                                <div class="input-group ">
                                                    <span class="input-group-text mobile-code">

                                                    </span>
                                                    <input type="hidden" name="mobile_code">
                                                    <input type="hidden" name="country_code">
                                                    <input type="number" name="mobile" value="{{ old('mobile') }}"
                                                        class="form-control form--control checkUser" required>
                                                </div>
                                                <small class="text--danger mobileExist"></small>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="form-label">@lang('Address')</label>
                                            <input type="text" class="form-control form--control" name="address"
                                                value="{{ old('address') }}">
                                        </div>
                                        <div class="form-group col-sm-6 state-field" style="display: none;">
                                            <label class="form-label">@lang('State') <span class="text--danger">*</span></label>
                                            <input type="text" class="form-control form--control" name="state"
                                                value="{{ old('state') }}">
                                        </div>
                                        <div class="form-group col-sm-6 zip-field" style="display: none;">
                                            <label class="form-label">@lang('Zip Code') <span class="text--danger">*</span></label>
                                            <input type="text" class="form-control form--control" name="zip"
                                                value="{{ old('zip') }}">
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="form-label">@lang('City')</label>
                                            <input type="text" class="form-control form--control" name="city"
                                                value="{{ old('city') }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn--base h-45 w-100">
                                            @lang('Submit')
                                        </button>
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


@push('style')
    <style>
        @media(max-width: 991px) {
            .login-form {
                padding-top: 32px !important
            }
        }
    </style>
@endpush


@push('style-lib')
<link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
<script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush



@push('script')
<script>
    "use strict";
        (function($) {

            @if($mobileCode)
            $(`option[data-code={{ $mobileCode }}]`).attr('selected','');
            @endif

            $('.select2').select2();

            // Countries that have states/provinces
            // SAFE: getCountriesWithStatesJson() returns hardcoded JSON array, no user data
            const countriesWithStates = {!! getCountriesWithStatesJson() !!};
            
            function toggleStateZipFields(countryCode) {
                const hasStates = countriesWithStates.includes(countryCode);
                if (hasStates) {
                    $('.state-field, .zip-field').slideDown(300);
                    $('.state-field input, .zip-field input').prop('required', true);
                } else {
                    $('.state-field, .zip-field').slideUp(300);
                    $('.state-field input, .zip-field input').prop('required', false).val('');
                }
            }
            
            $('select[name=country]').on('change',function() {
                var countryCode = $('select[name=country] :selected').data('code');
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val(countryCode);
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
                toggleStateZipFields(countryCode);
                var value = $('[name=mobile]').val();
                var name = 'mobile';
                checkUser(value,name);
            });
            
            // Initialize on page load
            var initialCountryCode = $('select[name=country] :selected').data('code');
            if (initialCountryCode) {
                toggleStateZipFields(initialCountryCode);
            }

            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));


            $('.checkUser').on('focusout', function(e) {
                var value = $(this).val();
                var name = $(this).attr('name')
                checkUser(value,name);
            });

            function checkUser(value,name){
                var url = '{{ route('user.checkUser') }}';
                var token = '{{ csrf_token() }}';

                if (name == 'mobile') {
                    var mobile = `${value}`;
                    var data = {
                        mobile: mobile,
                        mobile_code:$('.mobile-code').text().substr(1),
                        _token: token
                    }
                }
                if (name == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                     if (response.data != false) {
                        $(`.${response.type}Exist`).text(`${response.field} already exist`);
                    } else {
                        $(`.${response.type}Exist`).text('');
                    }
                });
            }
        })(jQuery);
</script>
@endpush