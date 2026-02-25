@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    @if($watchlist->count() > 0)
            <div class="row g-4">
                @foreach($watchlist as $watch)
                    @if($watch->listing)
                        <div class="col-md-6 col-lg-4">
                            <div class="listing-card card h-100 shadow-sm">
                                <div class="card-img-top position-relative">
                                    @if($watch->listing->images->first())
                                        <img src="{{ getImage(getFilePath('listing') . '/' . $watch->listing->images->first()->image) }}" 
                                             alt="{{ $watch->listing->title }}" class="img-fluid" style="height: 180px; object-fit: cover; width: 100%;">
                                    @else
                                        <div class="placeholder-img bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                            <i class="las la-image fs-1 text-muted"></i>
                                        </div>
                                    @endif
                                    
                                    <form action="{{ route('user.watchlist.remove', $watch->id) }}" method="POST" 
                                          class="position-absolute top-0 end-0 m-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="@lang('Remove from watchlist')">
                                            <i class="las la-heart-broken"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="card-body">
                                    <span class="badge bg-secondary mb-2">
                                        {{ ucfirst(str_replace('_', ' ', $watch->listing->business_type)) }}
                                    </span>
                                    
                                    <h5 class="card-title">
                                        <a href="{{ route('marketplace.listing.show', $watch->listing->slug) }}" class="text-dark text-decoration-none">
                                            {{ Str::limit($watch->listing->title, 50) }}
                                        </a>
                                    </h5>
                                    
                                    @if($watch->listing->sale_type === 'auction')
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted">@lang('Current Bid')</small>
                                                <strong class="d-block text--base">
                                                    {{ showAmount($watch->listing->current_bid ?: $watch->listing->starting_bid) }}
                                                </strong>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">@lang('Ends')</small>
                                                <span class="d-block text-danger">
                                                    {{ $watch->listing->auction_end ? $watch->listing->auction_end->diffForHumans() : 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <div>
                                            <small class="text-muted">@lang('Asking Price')</small>
                                            <strong class="d-block text--base">{{ showAmount($watch->listing->asking_price) }}</strong>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="card-footer bg-white">
                                    <a href="{{ route('marketplace.listing.show', $watch->listing->slug) }}" class="btn btn--base btn-sm w-100">
                                        @lang('View Listing')
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            
            @if($watchlist->hasPages())
                <div class="mt-4">{{ $watchlist->links() }}</div>
            @endif
        @else
            <div class="text-center py-5">
                <x-empty-state
                    title="Your watchlist is empty"
                    message="Start watching listings to track them here"
                    actionHref="{{ route('marketplace.browse') }}"
                    actionLabel="Browse Listings"
                    imgMaxWidth="150"
                />
            </div>
        @endif
@endsection

