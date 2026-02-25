@extends($activeTemplate . 'user.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-8">

                    <div class="card custom--card">
                        <div class="card-body">

                            <form action="" method="post">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">@lang('Current Password')</label>
                                    <input autocomplete="current-password" class="form-control form--control" name="current_password" required type="password">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">@lang('Password')</label>
                                    <input autocomplete="current-password" class="form-control form--control @if (gs('secure_password')) secure-password @endif" name="password" required type="password">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">@lang('Confirm Password')</label>
                                    <input autocomplete="current-password" class="form-control form--control" name="password_confirmation" required type="password">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn--base h-45 w-100 fw-bold" type="submit">@lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                    </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .card button[type=submit].btn--base {
            background: #{{ gs('base_color', '4bea76') }} !important;
            color: #fff !important;
            font-weight: 600 !important;
            border: none !important;
        }
        
        .card button[type=submit].btn--base:hover {
            background: #{{ gs('base_color', '4bea76') }} !important;
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
        }
    </style>
@endpush

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
