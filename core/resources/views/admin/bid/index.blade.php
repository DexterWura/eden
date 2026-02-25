@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Bid #')</th>
                                <th>@lang('Listing')</th>
                                <th>@lang('Bidder')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bids as $bid)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $bid->bid_number }}</span>
                                        @if($bid->is_buy_now)
                                            <br><span class="badge badge--warning">@lang('Buy Now')</span>
                                        @endif
                                        @if($bid->is_auto_bid)
                                            <br><span class="badge badge--info">@lang('Auto Bid')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bid->listing)
                                            <a href="{{ route('admin.listing.details', $bid->listing->id) }}">
                                                {{ Str::limit($bid->listing->title, 30) }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bid->user)
                                            <a href="{{ route('admin.users.detail', $bid->user->id) }}">
                                                {{ $bid->user->username }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ showAmount($bid->amount) }}</span>
                                        @if($bid->max_bid > 0)
                                            <br><small class="text-muted">@lang('Max'): {{ showAmount($bid->max_bid) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php echo $bid->bidStatus @endphp
                                    </td>
                                    <td>
                                        {{ showDateTime($bid->created_at) }}
                                        <br>
                                        <small>{{ diffForHumans($bid->created_at) }}</small>
                                    </td>
                                    <td>
                                        <div class="button-group">
                                            @if(in_array($bid->status, [\App\Constants\Status::BID_ACTIVE, \App\Constants\Status::BID_WINNING]))
                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn"
                                                        data-action="{{ route('admin.bid.cancel', $bid->id) }}"
                                                        data-question="@lang('Are you sure to cancel this bid?')">
                                                    <i class="la la-times"></i> @lang('Cancel')
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __($emptyMessage ?? 'No data found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($bids->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($bids) }}
                </div>
            @endif
        </div>
    </div>
</div>
<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
<x-search-form placeholder="Search by Bid #, Username, Listing" />
@endpush

