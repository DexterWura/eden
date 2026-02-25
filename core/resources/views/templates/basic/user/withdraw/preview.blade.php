@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card b-radius--10">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="las la-money-check-alt text--base me-2"></i>
                        @lang('Withdraw Via') {{ $withdraw->method->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="mb-0">
                            <i class="las la-info-circle me-2"></i>
                            @lang('You are requesting') <strong>{{ showAmount($withdraw->amount) }}</strong> @lang('for withdraw.')
                            @lang('The admin will send you')
                            <strong class="text--success">{{ showAmount($withdraw->final_amount, currencyFormat: false) . ' ' . $withdraw->currency }}</strong>
                            @lang('to your account.')
                        </p>
                    </div>
                    
                    <form action="{{ route('user.withdraw.submit') }}" class="disableSubmission" method="post" enctype="multipart/form-data">
                        @csrf
                        
                        @if($withdraw->method->description)
                            <div class="mb-4">
                                <div class="form-text">
                                    @php
                                        echo $withdraw->method->description;
                                    @endphp
                                </div>
                            </div>
                        @endif
                        
                        <x-viser-form identifier="id" identifierValue="{{ $withdraw->method->form_id }}" />
                        
                        @if(auth()->user()->ts)
                            <div class="form-group mb-4">
                                <label class="form-label">@lang('Google Authenticator Code')</label>
                                <input type="text" name="authenticator_code" class="form-control form--control" required>
                            </div>
                        @endif
                        
                        <div class="d-flex gap-2">
                            <a href="{{ route('user.withdraw') }}" class="btn btn-outline--secondary flex-fill">
                                <i class="las la-arrow-left me-2"></i> @lang('Back')
                            </a>
                            <button type="submit" class="btn btn--base flex-fill fw-bold">
                                <i class="las la-check-circle me-2"></i> @lang('Submit')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('style')
    <style>
        .disableSubmission button[type=submit].btn--base {
            background: #{{ gs('base_color', '4bea76') }} !important;
            color: #fff !important;
            font-weight: 600 !important;
            border: none !important;
        }
        
        .disableSubmission button[type=submit].btn--base:hover:not(:disabled) {
            background: #{{ gs('base_color', '4bea76') }} !important;
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
        }
        
        .btn-outline--secondary {
            color: hsl(var(--secondary)) !important;
            border-color: hsl(var(--secondary)) !important;
        }
        
        .btn-outline--secondary:hover {
            background-color: hsl(var(--secondary)) !important;
            color: hsl(var(--white)) !important;
        }
    </style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush