@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row gy-4">
                <div class="col-lg-4">
                    <div class="card custom--card">
                        <div class="card-body">
                            <ul class="list-group list-group-flush ">
                                <li class="list-group-item d-flex flex-column justify-content-between border-0 bg-transparent">
                                    <span class="fw-md text-muted">{{ $user->username }}</span>
                                    <small class="text-muted"> <i class="la la-user"></i> @lang('Userame')</small>
                                </li>

                                <li class="list-group-item d-flex flex-column justify-content-between border-0 bg-transparent">
                                    <span class="fw-md text-muted">{{ $user->email }}</span>
                                    <small class="text-muted"><i class="la la-envelope"></i> @lang('Email')</small>
                                </li>

                                <li class="list-group-item d-flex flex-column justify-content-between border-0 bg-transparent">
                                    <span class="fw-md text-muted">+{{ $user->mobile }}</span>
                                    <small class="text-muted"><i class="la la-mobile"></i> @lang('Mobile')</small>
                                </li>

                                <li class="list-group-item d-flex flex-column justify-content-between border-0 bg-transparent">
                                    <span class="fw-md text-muted">{{ $user->country_name }}</span>
                                    <small class="text-muted"><i class="la la-globe"></i> @lang('Country')</small>
                                </li>

                                <li class="list-group-item d-flex flex-column justify-content-between border-0 bg-transparent">
                                    <span class="fw-md text-muted">{{ $user->address }}</span>
                                    <small class="text-muted"><i class="la la-map-marked"></i> @lang('Address')</small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card custom--card">
                        <div class="card-body">

                            <form class="register" action="" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="form-group  col-sm-6">
                                        <label class="form-label">@lang('First Name')</label>
                                        <input type="text" class="form-control form--control" name="firstname" value="{{ $user->firstname }}" required>
                                    </div>
                                    <div class="form-group  col-sm-6">
                                        <label class="form-label">@lang('Last Name')</label>
                                        <input type="text" class="form-control form--control" name="lastname" value="{{ $user->lastname }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group  col-sm-12">
                                        <label class="form-label">@lang('Address')</label>
                                        <input type="text" class="form-control form--control" name="address" value="{{ @$user->address }}">
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="form-group col-sm-6 state-field" style="display: {{ countryHasStates($user->country_code) ? 'block' : 'none' }};">
                                        <label class="form-label">@lang('State') @if(countryHasStates($user->country_code))<span class="text--danger">*</span>@endif</label>
                                        <input type="text" class="form-control form--control" name="state" value="{{ @$user->state }}" @if(countryHasStates($user->country_code)) required @endif>
                                    </div>
                                    <div class="form-group col-sm-6 zip-field" style="display: {{ countryHasStates($user->country_code) ? 'block' : 'none' }};">
                                        <label class="form-label">@lang('Zip Code') @if(countryHasStates($user->country_code))<span class="text--danger">*</span>@endif</label>
                                        <input type="text" class="form-control form--control" name="zip" value="{{ @$user->zip }}" @if(countryHasStates($user->country_code)) required @endif>
                                    </div>

                                </div>

                                <button type="submit" class="btn btn--base h-45 w-100 fw-bold">@lang('Submit')</button>

                            </form>
                        </div>
                    </div>
                </div>
    </div>
@endsection

@push('style')
    <style>
        .register button[type=submit].btn--base {
            background: #{{ gs('base_color', '4bea76') }} !important;
            color: #fff !important;
            font-weight: 600 !important;
            border: none !important;
        }
        
        .register button[type=submit].btn--base:hover {
            background: #{{ gs('base_color', '4bea76') }} !important;
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
        }
        
        /* Fix form label colors - ensure all labels are visible with black text */
        .form-label {
            color: #000000 !important;
        }
        
        label.form-label {
            color: #000000 !important;
        }
    </style>
@endpush
