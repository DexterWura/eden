@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('user.offer.received') }}" class="btn btn--base btn-sm" id="receivedOffersBtn">
                    <i class="las la-inbox"></i> @lang('Received Offers')
                </a>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">@lang('All Status')</option>
                                <option value="{{ \App\Constants\Status::OFFER_PENDING }}" @selected(request('status') == \App\Constants\Status::OFFER_PENDING)>@lang('Pending')</option>
                                <option value="{{ \App\Constants\Status::OFFER_ACCEPTED }}" @selected(request('status') == \App\Constants\Status::OFFER_ACCEPTED)>@lang('Accepted')</option>
                                <option value="{{ \App\Constants\Status::OFFER_REJECTED }}" @selected(request('status') == \App\Constants\Status::OFFER_REJECTED)>@lang('Rejected')</option>
                                <option value="{{ \App\Constants\Status::OFFER_COUNTERED }}" @selected(request('status') == \App\Constants\Status::OFFER_COUNTERED)>@lang('Countered')</option>
                                <option value="{{ \App\Constants\Status::OFFER_EXPIRED }}" @selected(request('status') == \App\Constants\Status::OFFER_EXPIRED)>@lang('Expired')</option>
                                <option value="{{ \App\Constants\Status::OFFER_CANCELLED }}" @selected(request('status') == \App\Constants\Status::OFFER_CANCELLED)>@lang('Cancelled')</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="@lang('Search by listing title...')">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn--base w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                        </div>
                        <div class="col-md-3 text-end">
                            @if(request()->has('status') || request()->has('search'))
                                <a href="{{ route('user.offer.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="las la-times"></i> @lang('Clear')
                                </a>
                            @endif
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
                            <th>@lang('Listing')</th>
                            <th>@lang('My Offer')</th>
                            <th>@lang('Asking Price')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Date')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offers as $offer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-3">
                                            <x-table-thumb
                                                :src="$offer->listing->images->first() ? getImage(getFilePath('listing') . '/' . $offer->listing->images->first()->image) : null"
                                                alt=""
                                            />
                                        </span>
                                        <div>
                                            <a href="{{ route('marketplace.listing.show', $offer->listing->slug) }}">
                                                {{ Str::limit($offer->listing->title, 40) }}
                                            </a>
                                            <br>
                                            <small class="text-muted">@lang('Seller'): {{ $offer->seller->username ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ showAmount($offer->amount) }}</strong>
                                    @if($offer->counter_amount > 0)
                                        <br>
                                        <small class="text-info">@lang('Counter'): {{ showAmount($offer->counter_amount) }}</small>
                                    @endif
                                </td>
                                <td>{{ showAmount($offer->listing->asking_price) }}</td>
                                <td>@php echo $offer->offerStatus @endphp</td>
                                <td>{{ $offer->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="button--group">
                                        @if($offer->status == \App\Constants\Status::OFFER_COUNTERED)
                                            <form action="{{ route('user.offer.accept.counter', $offer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--success" 
                                                        onclick="return confirm('@lang('Accept counter offer of') {{ showAmount($offer->counter_amount) }}?')">
                                                    <i class="las la-check"></i> @lang('Accept')
                                                </button>
                                            </form>
                                        @endif
                                        @if(in_array($offer->status, [\App\Constants\Status::OFFER_PENDING, \App\Constants\Status::OFFER_COUNTERED]))
                                            <form action="{{ route('user.offer.cancel', $offer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger" 
                                                        onclick="return confirm('@lang('Cancel this offer?')')">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($offer->status == \App\Constants\Status::OFFER_ACCEPTED && $offer->escrow_id)
                                            <a href="{{ route('user.escrow.details', $offer->escrow_id) }}" class="btn btn-sm btn-outline--primary">
                                                <i class="las la-hand-holding-usd"></i> @lang('Pay')
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <x-empty-state
                                        title="No offers made yet"
                                        actionHref="{{ route('marketplace.browse') }}"
                                        actionLabel="Browse Listings"
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
            
            @if($offers->hasPages())
                <div class="mt-4">{{ $offers->links() }}</div>
            @endif
        </div>
    </div>
@endsection

{{-- styles moved to assets/templates/basic/css/custom.css --}}

