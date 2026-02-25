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
                                <th>@lang('Offer #')</th>
                                <th>@lang('Listing')</th>
                                <th>@lang('Buyer')</th>
                                <th>@lang('Seller')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offers as $offer)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $offer->offer_number }}</span>
                                    </td>
                                    <td>
                                        @if($offer->listing)
                                            <a href="{{ route('admin.listing.details', $offer->listing->id) }}">
                                                {{ Str::limit($offer->listing->title, 25) }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($offer->buyer)
                                            <a href="{{ route('admin.users.detail', $offer->buyer->id) }}">
                                                {{ $offer->buyer->username }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($offer->seller)
                                            <a href="{{ route('admin.users.detail', $offer->seller->id) }}">
                                                {{ $offer->seller->username }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ showAmount($offer->amount) }}</span>
                                        @if($offer->counter_amount > 0)
                                            <br><small class="text-info">@lang('Counter'): {{ showAmount($offer->counter_amount) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php echo $offer->offerStatus @endphp
                                        @if($offer->expires_at && $offer->expires_at->isFuture())
                                            <br><small class="text-muted">@lang('Expires'): {{ $offer->expires_at->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ showDateTime($offer->created_at) }}
                                        <br>
                                        <small>{{ diffForHumans($offer->created_at) }}</small>
                                    </td>
                                    <td>
                                        <div class="button-group">
                                            @if(in_array($offer->status, [\App\Constants\Status::OFFER_PENDING, \App\Constants\Status::OFFER_COUNTERED]))
                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn"
                                                        data-action="{{ route('admin.offer.cancel', $offer->id) }}"
                                                        data-question="@lang('Are you sure to cancel this offer?')">
                                                    <i class="la la-times"></i> @lang('Cancel')
                                                </button>
                                            @endif
                                            @if($offer->escrow_id)
                                                <a href="{{ route('admin.escrow.details', $offer->escrow_id) }}" 
                                                   class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-handshake"></i> @lang('Escrow')
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __($emptyMessage ?? 'No data found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($offers->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($offers) }}
                </div>
            @endif
        </div>
    </div>
</div>
<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
<x-search-form placeholder="Search by Offer #, Username, Listing" />
@endpush

