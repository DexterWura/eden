@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('user.bid.won') }}" class="btn btn--base btn-sm" id="wonAuctionsBtn">
                    <i class="las la-trophy"></i> @lang('Won Auctions')
                </a>
            </div>
            
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
                            <th>@lang('My Bid')</th>
                            <th>@lang('Current Bid')</th>
                            <th>@lang('Ends In')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bids as $bid)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-3">
                                            <x-table-thumb
                                                :src="$bid->listing->images->first() ? getImage(getFilePath('listing') . '/' . $bid->listing->images->first()->image) : null"
                                                alt=""
                                            />
                                        </span>
                                        <div>
                                            <a href="{{ route('marketplace.listing.show', $bid->listing->slug) }}">
                                                {{ Str::limit($bid->listing->title, 40) }}
                                            </a>
                                            <br>
                                            <small class="text-muted">@lang('by') {{ $bid->listing->seller->username ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong class="{{ $bid->status == \App\Constants\Status::BID_WINNING ? 'text-success' : '' }}">
                                        {{ showAmount($bid->amount) }}
                                    </strong>
                                    @if($bid->max_bid > 0)
                                        <br>
                                        <small class="text-muted">@lang('Max'): {{ showAmount($bid->max_bid) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ showAmount($bid->listing->current_bid) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $bid->listing->total_bids }} @lang('bids')</small>
                                </td>
                                <td>
                                    @if($bid->listing->auction_end)
                                        @if($bid->listing->auction_end->isFuture())
                                            <span class="text-danger">{{ $bid->listing->auction_end->diffForHumans() }}</span>
                                        @else
                                            <span class="text-muted">@lang('Ended')</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>@php echo $bid->bidStatus @endphp</td>
                                <td>
                                    <a href="{{ route('marketplace.listing.show', $bid->listing->slug) }}" 
                                       class="btn btn-sm btn-outline--primary">
                                        <i class="las la-desktop"></i> @lang('View')
                                    </a>
                                    @if(in_array($bid->status, [\App\Constants\Status::BID_ACTIVE, \App\Constants\Status::BID_WINNING]))
                                        @if($bid->listing->auction_end && $bid->listing->auction_end->diffInHours(now()) >= 24)
                                            <form action="{{ route('user.bid.cancel', $bid->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger" 
                                                        onclick="return confirm('@lang('Are you sure?')')">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <x-empty-state
                                        title="No bids placed yet"
                                        actionHref="{{ route('marketplace.auctions') }}"
                                        actionLabel="Browse Auctions"
                                        imgMaxWidth="120"
                                    />
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            
            @if($bids->hasPages())
                <div class="mt-4">{{ $bids->links() }}</div>
            @endif
        </div>
    </div>
@endsection

{{-- styles moved to assets/templates/basic/css/custom.css --}}

