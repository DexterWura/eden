@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="section">
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar bg-white rounded shadow-sm p-4">
                    <h5 class="mb-4">@lang('Filters')</h5>
                    <form action="{{ route('tools.payback') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label">@lang('Search')</label>
                            <input type="text" name="search" class="form-control"
                                   value="{{ request('search') }}" placeholder="@lang('Search listings...')">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">@lang('Business Type')</label>
                            <select name="business_type" class="form-select">
                                <option value="">@lang('All Types')</option>
                                @foreach($businessTypes as $key => $name)
                                    <option value="{{ $key }}" @selected(request('business_type') == $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">@lang('Category')</label>
                            <select name="category" class="form-select">
                                <option value="">@lang('All Categories')</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                        <div class="mb-3">
                            <label class="form-label">@lang('Monthly Revenue')</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_revenue" class="form-control"
                                           value="{{ request('min_revenue') }}" placeholder="@lang('Min')">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_revenue" class="form-control"
                                           value="{{ request('max_revenue') }}" placeholder("@lang('Max')">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--base w-100">
                            <i class="las la-search"></i> @lang('Search')
                        </button>
                        <a href="{{ route('tools.payback') }}" class="btn btn-outline-secondary w-100 mt-2">@lang('Clear')</a>
                    </form>
                </div>
            </div>
            <!-- Listings -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0 text-muted">
                        @lang('Showing') {{ $listings->firstItem() ?? 0 }} - {{ $listings->lastItem() ?? 0 }}
                        @lang('of') {{ $listings->total() }} @lang('listings')
                    </p>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted me-2">@lang('Sort by'):</label>
                        <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" @selected(request('sort') == 'newest' || !request('sort'))>@lang('Newest')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" @selected(request('sort') == 'price_low')>@lang('Price: Low to High')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" @selected(request('sort') == 'price_high')>@lang('Price: High to Low')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'revenue_high']) }}" @selected(request('sort') == 'revenue_high')>@lang('Revenue: High to Low')</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'revenue_low']) }}" @selected(request('sort') == 'revenue_low')>@lang('Revenue: Low to High')</option>
                        </select>
                    </div>
                </div>
                @if($listings->count() > 0)
                    <div class="row g-4">
                        @foreach($listings as $listing)
                            @include($activeTemplate . 'partials.listing_card', ['listing' => $listing, 'showPaybackButton' => true])
                        @endforeach
                    </div>
                    @if($listings->hasPages())
                        <div class="mt-4">{{ $listings->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="las la-search text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">@lang('No listings found')</h5>
                        <p class="text-muted">@lang('Try adjusting your filters or search terms')</p>
                        <a href="{{ route('tools.payback') }}" class="btn btn--base mt-2">@lang('Clear filters')</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="{{ route('tools.index') }}" class="text-muted small"><i class="las la-arrow-left me-1"></i> @lang('Back to all tools')</a>
            </div>
        </div>
    </div>
</section>
@endsection
