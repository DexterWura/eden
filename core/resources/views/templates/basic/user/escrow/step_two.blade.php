@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section bg--light">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-body">
                            <form action="{{ route('user.escrow.step.two.submit') }}" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label class="d-block mb-2 sm-text">
                                        @if ($escrowInfo['type'] == 1)
                                            @lang('Buyer\'s')
                                        @else
                                            @lang('Seller\'s')
                                        @endif @lang('Email')
                                    </label>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="@lang('Enter email')"
                                        class="form-control form--control" required>
                                </div>
                                <div class="form-group">
                                    <label class="d-block mb-2 sm-text">@lang('Title')</label>
                                    <input type="text" name="title" value="{{ old('title') }}" placeholder="@lang('Enter title')"
                                        class="form-control form--control" required>
                                </div>

                                <div class="form-group">
                                    <label class="d-block mb-2 sm-text">@lang('Amount')</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form--control" value="{{ getAmount($escrowInfo['amount']) }}" readonly>
                                        <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="d-block mb-2 sm-text">@lang('Charge')</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form--control" value="{{ getAmount($escrowInfo['charge']) }}" readonly>
                                        <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                    </div>
                                </div>
                                <div class="form-group select2-parent">
                                    <label class="d-block mb-2 sm-text">@lang('Charge will Pay')</label>

                                    <select name="charge_payer" class="form-control select2-basic" data-minimum-results-for-search="-1" required>
                                        <option value="1">@lang('Seller')</option>
                                        <option value="2">@lang('Buyer')</option>
                                        <option value="3">@lang('50% - 50%')</option>
                                    </select>

                                </div>
                                <div class="form-group">
                                    <label class="d-block mb-2 sm-text">@lang('Details')</label>
                                    <textarea name="details" class="form-control form--control-textarea" rows="5" placeholder="@lang('Enter details')">{{ old('details') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn--base w-100 h-45">@lang('Next')</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush
