@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="py-5">
    <div class="container">
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">@lang('Business Type')</label>
                        <select name="business_type" class="form-select">
                            <option value="">@lang('All Types')</option>
                            @foreach($businessTypes as $key => $name)
                                <option value="{{ $key }}" @selected(request('business_type') == $key)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">@lang('Filter')</label>
                        <select name="ending" class="form-select">
                            <option value="">@lang('All Auctions')</option>
                            <option value="soon" @selected(request('ending') == 'soon')>@lang('Ending Within 24 Hours')</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn--base w-100">
                            <i class="las la-filter"></i> @lang('Apply Filter')
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Auction Listings -->
        @if($listings->count() > 0)
            <div class="row g-4">
                @foreach($listings as $listing)
                    <div class="col-md-6 col-lg-4">
                        <div class="listing-card card h-100 shadow-sm border-0">
                            <div class="card-img-top position-relative">
                                @if($listing->primaryImage)
                                    <img src="{{ getImage(getFilePath('listing') . '/' . $listing->primaryImage->image) }}" 
                                         alt="{{ $listing->title }}" class="img-fluid" style="height: 180px; object-fit: cover; width: 100%;">
                                @else
                                    <div class="placeholder-img bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                        <i class="las la-image fs-1 text-muted"></i>
                                    </div>
                                @endif
                                
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    <i class="las la-gavel"></i> @lang('Auction')
                                </span>
                                
                                @if($listing->is_verified)
                                    <span class="badge bg-success position-absolute bottom-0 start-0 m-2">
                                        <i class="las la-check-circle"></i> @lang('Verified')
                                    </span>
                                @endif
                            </div>
                            
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2">
                                    {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                                </span>
                                
                                <h5 class="card-title">
                                    <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="text-dark text-decoration-none">
                                        {{ Str::limit($listing->title, 50) }}
                                    </a>
                                </h5>
                                
                                <div class="auction-details mt-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded text-center">
                                                <small class="text-muted d-block">@lang('Current Bid')</small>
                                                <strong class="text--base">
                                                    {{ $listing->current_bid > 0 ? showAmount($listing->current_bid) : showAmount($listing->starting_bid) }}
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded text-center">
                                                <small class="text-muted d-block">@lang('Bids')</small>
                                                <strong>{{ $listing->total_bids }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 p-2 bg-danger bg-opacity-10 rounded text-center">
                                        <small class="text-muted d-block">@lang('Time Remaining')</small>
                                        <strong class="text-danger">
                                            @if($listing->auction_end && $listing->auction_end->isFuture())
                                                {{ $listing->auction_end->diffForHumans() }}
                                            @else
                                                @lang('Ended')
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-white border-top-0">
                                <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="btn btn--base btn-sm w-100">
                                    <i class="las la-gavel"></i> @lang('View & Bid')
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($listings->hasPages())
                <div class="mt-4">
                    {{ $listings->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <img src="{{ asset('assets/images/empty_list.png') }}" alt="No auctions" class="mb-3" style="max-width: 200px;">
                <h5>@lang('No active auctions')</h5>
                <p class="text-muted">@lang('Check back soon for new auctions')</p>
                <a href="{{ route('marketplace.browse') }}" class="btn btn--base">
                    <i class="las la-search"></i> @lang('Browse Fixed Price Listings')
                </a>
            </div>
        @endif
    </div>
</section>
@endsection

@push('style')
<style>
    .bg--gradient {
        background: linear-gradient(135deg, var(--base-color) 0%, #1a1a2e 100%);
    }
</style>
@endpush

