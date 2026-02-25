@extends($activeTemplate . 'user.layouts.app')

@section('panel')
<div class="dashboard-body-part">
    <div class="card custom--card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="card-title mb-0">
                <i class="las la-trophy text-warning me-2"></i>@lang('Won Auctions')
            </h5>
            <a href="{{ route('user.bid.index') }}" class="btn btn--sm btn--dark">
                <i class="las la-arrow-left"></i> @lang('All Bids')
            </a>
        </div>
        <div class="card-body">
            @if($bids->count() > 0)
            <div class="table-responsive">
                <table class="table table--striped">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
                            <th>@lang('Winning Bid')</th>
                            <th>@lang('Won On')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bids as $bid)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($bid->listing && $bid->listing->images->first())
                                        <img src="{{ getImage(getFilePath('listing') . '/' . $bid->listing->images->first()->image) }}" 
                                             alt="" style="width: 50px; height: 40px; object-fit: cover; border-radius: 5px;">
                                    @endif
                                    <div>
                                        @if($bid->listing)
                                            <a href="{{ route('marketplace.listing.show', $bid->listing->slug) }}">
                                                <strong>{{ Str::limit($bid->listing->title, 30) }}</strong>
                                            </a>
                                            <br>
                                            <small class="text-muted">
                                                @if($bid->listing->seller)
                                                    @lang('Seller'): {{ $bid->listing->seller->username }}
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">@lang('Listing not available')</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong class="text--base">{{ showAmount($bid->amount) }}</strong>
                                @if($bid->is_buy_now)
                                    <br><span class="badge badge--warning">@lang('Buy Now')</span>
                                @endif
                            </td>
                            <td>
                                {{ showDateTime($bid->updated_at, 'd M, Y') }}
                                <br>
                                <small class="text-muted">{{ diffForHumans($bid->updated_at) }}</small>
                            </td>
                            <td>
                                @if($bid->listing && $bid->listing->escrow_id)
                                    @php
                                        $escrow = $bid->listing->escrow;
                                    @endphp
                                    @if($escrow)
                                        @if($escrow->status == \App\Constants\Status::ESCROW_ACCEPTED)
                                            <span class="badge badge--info">@lang('Awaiting Payment')</span>
                                        @elseif($escrow->status == \App\Constants\Status::ESCROW_COMPLETED)
                                            <span class="badge badge--success">@lang('Completed')</span>
                                        @else
                                            <span class="badge badge--warning">@lang('In Progress')</span>
                                        @endif
                                    @endif
                                @else
                                    <span class="badge badge--primary">@lang('Won')</span>
                                @endif
                            </td>
                            <td>
                                @if($bid->listing && $bid->listing->escrow_id)
                                    <a href="{{ route('user.escrow.details', $bid->listing->escrow_id) }}" 
                                       class="btn btn-sm btn-outline--primary">
                                        <i class="las la-handshake"></i> @lang('View Escrow')
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($bids->hasPages())
                {{ paginateLinks($bids) }}
            @endif
            @else
            <div class="text-center py-5">
                <i class="las la-trophy display-3 text-muted mb-3"></i>
                <h5 class="text-muted">@lang('No won auctions yet')</h5>
                <p class="text-muted">@lang('Your winning bids will appear here')</p>
                <a href="{{ route('marketplace.auctions') }}" class="btn btn--base">
                    <i class="las la-gavel"></i> @lang('Browse Auctions')
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

