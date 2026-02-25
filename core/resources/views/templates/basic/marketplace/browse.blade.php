@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar bg-white rounded shadow-sm p-4">
                    <h5 class="mb-4">@lang('Filters')</h5>
                    
                    <form action="{{ route('marketplace.browse') }}" method="GET">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Search')</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="@lang('Search listings...')">
                        </div>
                        
                        <!-- Business Type -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Business Type')</label>
                            <select name="business_type" class="form-select">
                                <option value="">@lang('All Types')</option>
                                @foreach($businessTypes as $key => $name)
                                    <option value="{{ $key }}" @selected(request('business_type') == $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Sale Type -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Sale Type')</label>
                            <select name="sale_type" class="form-select">
                                <option value="">@lang('All')</option>
                                <option value="fixed_price" @selected(request('sale_type') == 'fixed_price')>@lang('Fixed Price')</option>
                                <option value="auction" @selected(request('sale_type') == 'auction')>@lang('Auction')</option>
                            </select>
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Category')</label>
                            <select name="category" class="form-select">
                                <option value="">@lang('All Categories')</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Price Range')</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control" 
                                           value="{{ request('min_price') }}" placeholder="@lang('Min')">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control" 
                                           value="{{ request('max_price') }}" placeholder="@lang('Max')">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Revenue Range -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Monthly Revenue')</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_revenue" class="form-control" 
                                           value="{{ request('min_revenue') }}" placeholder="@lang('Min')">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_revenue" class="form-control" 
                                           value="{{ request('max_revenue') }}" placeholder="@lang('Max')">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Traffic Range (monthly visitors) -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Monthly Visitors')</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_traffic" class="form-control" 
                                           value="{{ request('min_traffic') }}" placeholder="@lang('Min')" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_traffic" class="form-control" 
                                           value="{{ request('max_traffic') }}" placeholder="@lang('Max')" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Age Range (domain/business age in years) -->
                        <div class="mb-3">
                            <label class="form-label">@lang('Business Age (years)')</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_age" class="form-control" 
                                           value="{{ request('min_age') }}" placeholder="@lang('Min')" min="0" step="0.5">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_age" class="form-control" 
                                           value="{{ request('max_age') }}" placeholder="@lang('Max')" min="0" step="0.5">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Featured Only -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="featured" value="1" class="form-check-input" 
                                       @checked(request('featured') == '1')>
                                <label class="form-check-label">@lang('Featured Only')</label>
                            </div>
                        </div>
                        
                        <!-- Verified Only -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="verified" value="1" class="form-check-input" 
                                       @checked(request('verified'))>
                                <label class="form-check-label">@lang('Verified Only')</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn--base w-100">
                            <i class="las la-filter"></i> @lang('Apply Filters')
                        </button>
                        
                        <a href="{{ route('marketplace.browse') }}" class="btn btn-outline-secondary w-100 mt-2">
                            @lang('Clear Filters')
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Listings -->
            <div class="col-lg-9">
                <!-- Sort & Results Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0 text-muted">
                        @lang('Showing') {{ $listings->firstItem() ?? 0 }} - {{ $listings->lastItem() ?? 0 }} 
                        @lang('of') {{ $listings->total() }} @lang('listings')
                    </p>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted me-2">@lang('Sort by'):</label>
                        <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" @selected(request('sort') == 'newest' || !request('sort'))>@lang('Newest')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}" @selected(request('sort') == 'oldest')>@lang('Oldest')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" @selected(request('sort') == 'price_low')>@lang('Price: Low to High')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" @selected(request('sort') == 'price_high')>@lang('Price: High to Low')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'revenue_high']) }}" @selected(request('sort') == 'revenue_high')>@lang('Revenue: High to Low')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'revenue_low']) }}" @selected(request('sort') == 'revenue_low')>@lang('Revenue: Low to High')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'traffic_high']) }}" @selected(request('sort') == 'traffic_high')>@lang('Traffic: High to Low')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'traffic_low']) }}" @selected(request('sort') == 'traffic_low')>@lang('Traffic: Low to High')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'ending_soon']) }}" @selected(request('sort') == 'ending_soon')>@lang('Ending Soon')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'most_bids']) }}" @selected(request('sort') == 'most_bids')>@lang('Most Bids')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'most_watched']) }}" @selected(request('sort') == 'most_watched')>@lang('Most Watched')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'most_viewed']) }}" @selected(request('sort') == 'most_viewed')>@lang('Most Viewed')</option>
                        </select>
                    </div>
                </div>
                
                <!-- Listings Grid -->
                @if($listings->count() > 0)
                    <div class="row g-4">
                        @foreach($listings as $listing)
                            @include($activeTemplate . 'partials.listing_card', ['listing' => $listing])
                        @endforeach
                    </div>
                    
                    @if($listings->hasPages())
                        <div class="mt-4">
                            {{ $listings->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('assets/images/empty_list.png') }}" alt="No listings" class="mb-3" style="max-width: 200px;">
                        <h5>@lang('No listings found')</h5>
                        <p class="text-muted">@lang('Try adjusting your filters or search terms')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

