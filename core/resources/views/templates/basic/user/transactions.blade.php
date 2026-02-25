@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
                    <div class="show-filter mb-3 text-end">
                        <button type="button" class="btn btn--base showFilterBtn btn-sm">
                            <i class="las la-filter"></i>
                            @lang('Filter')
                        </button>
                    </div>

                    <div class="card responsive-filter-card mb-4 custom--card">
                        <div class="card-body">
                            <form>
                                <div class="d-flex flex-wrap gap-4">
                                    <div class="flex-grow-1">
                                        <label class="form-label">@lang('Transaction Number')</label>
                                        <input type="search" name="search" value="{{ request()->search }}"
                                            class="form-control form--control">
                                    </div>
                                    <div class="flex-grow-1 select2-parent">
                                        <label class="form-label d-block">@lang('Type')</label>
                                        <select name="trx_type" class="form-select form--control select2-basic"
                                            data-minimum-results-for-search="-1">
                                            <option value="">@lang('All')</option>
                                            <option value="+" @selected(request()->trx_type == '+')>@lang('Plus')</option>
                                            <option value="-" @selected(request()->trx_type == '-')>@lang('Minus')</option>
                                        </select>
                                    </div>
                                    <div class="flex-grow-1 select2-parent">
                                        <label class="form-label d-block">@lang('Remark')</label>
                                        <select class="form-select form--control select2-basic"
                                            data-minimum-results-for-search="-1" name="remark">
                                            <option value="">@lang('All')</option>
                                            @foreach ($remarks as $remark)
                                                <option value="{{ $remark->remark }}" @selected(request()->remark == $remark->remark)>
                                                    {{ __(keyToTitle($remark->remark)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-grow-1 align-self-end">
                                        <button class="btn btn--base w-100 h-50">
                                            <i class="las la-filter"></i>
                                            @lang('Filter')
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Trx')</th>
                                <th>@lang('Transacted')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Post Balance')</th>
                                <th>@lang('Detail')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                <tr>
                                    <td>
                                        <strong>{{ $trx->trx }}</strong>
                                    </td>
                                    <td>
                                        {{ showDateTime($trx->created_at) }}<br>{{ diffForHumans($trx->created_at) }}
                                    </td>
                                    <td class="budget">
                                        <span
                                            class="fw-bold @if ($trx->trx_type == '+') text--success @else text--danger @endif">
                                            {{ $trx->trx_type }} {{ showAmount($trx->amount) }}

                                        </span>
                                    </td>
                                    <td class="budget">
                                        {{ showAmount($trx->post_balance) }}
                                    </td>
                                    <td>{{ __($trx->details) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($transactions->hasPages())
                <div class="mt-3">
                    {{ paginateLinks($transactions) }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

{{-- styles moved to assets/templates/basic/css/custom.css --}}
