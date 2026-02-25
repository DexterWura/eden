@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="section bg--light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card custom--card">
                    <div class="card-body">
                        <form action="{{ route('user.kyc.submit') }}" enctype="multipart/form-data" method="post">
                            @csrf
                            <x-viser-form identifierValue="kyc" identifier="act" />
                            
                                <button class="btn btn--base w-100 fw-bold" type="submit">@lang('Submit')</button>
                         
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('style')
    <style>
        .btn--base {
            background: #{{ gs('base_color', '4bea76') }} !important;
            color: #fff !important;
            font-weight: 600 !important;
            border: none !important;
        }
        
        .btn--base:hover {
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

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush
