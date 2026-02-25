@php
    $text = isset($register) ? 'Register' : 'Login';
@endphp
@if (
    @gs('socialite_credentials')->linkedin->status ||
        @gs('socialite_credentials')->facebook->status == Status::ENABLE ||
        @gs('socialite_credentials')->google->status == Status::ENABLE)

    <div class=" d-flex gap-3 flex-wrap">
        @if (@gs('socialite_credentials')->google->status == Status::ENABLE)
            <div class="flex-fill continue-google">
                <a href="{{ route('user.social.login', 'google') }}" class="btn  social-login-btn w-100">
                    <span class="google-icon">
                        <img src="{{ asset($activeTemplateTrue . 'images/google.svg') }}" alt="Google">
                    </span> @lang('Google')
                </a>
            </div>
        @endif

        @if (@gs('socialite_credentials')->facebook->status == Status::ENABLE)
            <div class="flex-fill continue-facebook">
                <a href="{{ route('user.social.login', 'facebook') }}" class="btn  social-login-btn w-100">
                    <span class="facebook-icon">
                        <img src="{{ asset($activeTemplateTrue . 'images/facebook.svg') }}" alt="Facebook">
                    </span> @lang('Facebook')
                </a>
            </div>
        @endif

        @if (@gs('socialite_credentials')->linkedin->status == Status::ENABLE)
            <div class="continue-facebook flex-fill">
                <a href="{{ route('user.social.login', 'linkedin') }}" class="btn  social-login-btn w-100">
                    <span class="facebook-icon">
                        <img src="{{ asset($activeTemplateTrue . 'images/linkdin.svg') }}" alt="Linkedin">
                    </span> @lang('Linkedin')
                </a>
            </div>
        @endif
    </div>
    <div class="text-center mt-4 mb-3">
        <span>@lang('OR')</span>
    </div>
@endif

@push('style')
    <style>
        .social-login-btn {
            border: 1px solid hsl(var(--border));
            padding: 12px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            gap: 5px;
            align-items: center;
            line-height: 1;
        }
    </style>
@endpush
