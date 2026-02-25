@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <!-- Hero Section -->
    <section class="marketplace-hero py-5 bg--gradient">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <p class="text-white-50 mb-4">@lang('Discover domains, websites, apps, and social media accounts for sale')</p>
                    
                    <form action="{{ route('marketplace.browse') }}" method="GET" class="search-form">
                        <div class="input-group input-group-lg">
                            <input type="text" name="search" class="form-control" placeholder="@lang('Search listings...')">
                            <select name="business_type" class="form-select" style="max-width: 180px;">
                                <option value="">@lang('All Types')</option>
                                <option value="domain">@lang('Domains')</option>
                                <option value="website">@lang('Websites')</option>
                                <option value="social_media_account">@lang('Social Media')</option>
                                <option value="mobile_app">@lang('Mobile Apps')</option>
                                <option value="desktop_app">@lang('Desktop Apps')</option>
                            </select>
                            <button type="submit" class="btn btn--base">
                                <i class="las la-search"></i> @lang('Search')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="py-5">
        <div class="container">
            <div class="section-header text-center mb-4">
                <h2>@lang('Browse by Category')</h2>
            </div>
            <div class="row g-3">
                @foreach(['domain' => 'Domain Names', 'website' => 'Websites', 'social_media_account' => 'Social Media', 'mobile_app' => 'Mobile Apps', 'desktop_app' => 'Desktop Apps'] as $type => $name)
                    <div class="col-6 col-md-4 col-lg">
                        <a href="{{ route('marketplace.type', $type) }}" class="category-card d-block p-4 text-center rounded bg-white shadow-sm h-100">
                            @php
                                $icons = [
                                    'domain' => 'la-globe',
                                    'website' => 'la-laptop',
                                    'social_media_account' => 'la-share-alt',
                                    'mobile_app' => 'la-mobile-alt',
                                    'desktop_app' => 'la-desktop'
                                ];
                            @endphp
                            <i class="las {{ $icons[$type] }} fs-1 text--base"></i>
                            <h6 class="mt-2 mb-0">{{ __($name) }}</h6>
                            <small class="text-muted">
                                {{ $categories->get($type)?->sum('listings_count') ?? 0 }} @lang('listings')
                            </small>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if($featuredListings->count() > 0)
    <section class="py-5 bg-light">
        <div class="container">
            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">@lang('Featured Listings')</h2>
                <a href="{{ route('marketplace.browse') }}?featured=1" class="btn btn--base btn-sm">@lang('View All')</a>
            </div>
            <div class="row g-4">
                @foreach($featuredListings as $listing)
                    @include($activeTemplate . 'partials.listing_card', ['listing' => $listing])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Ending Soon Auctions -->
    @if($endingSoon->count() > 0)
    <section class="py-5">
        <div class="container">
            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="las la-clock text--danger"></i> @lang('Ending Soon')
                </h2>
                <a href="{{ route('marketplace.auctions') }}?ending=soon" class="btn btn--base btn-sm">@lang('View All')</a>
            </div>
            <div class="row g-4">
                @foreach($endingSoon as $listing)
                    @include($activeTemplate . 'partials.listing_card', ['listing' => $listing])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Latest Listings -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">@lang('Latest Listings')</h2>
                <a href="{{ route('marketplace.browse') }}" class="btn btn--base btn-sm">@lang('Browse All')</a>
            </div>
            <div class="row g-4">
                @foreach($latestListings as $listing)
                    @include($activeTemplate . 'partials.listing_card', ['listing' => $listing])
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg--base">
        <div class="container text-center">
            <h2 class="text-white mb-3">@lang('Ready to Sell Your Business?')</h2>
            <p class="text-white-50 mb-4">@lang('List your domain, website, app or social media account and reach thousands of buyers')</p>
            <a href="{{ route('user.listing.create') }}" class="btn btn-light btn-lg">
                <i class="las la-plus"></i> @lang('Create Listing')
            </a>
        </div>
    </section>
@endsection

@push('style')
<style>
    .bg--gradient {
        background: linear-gradient(135deg, var(--base-color) 0%, #1a1a2e 100%);
    }
    .marketplace-hero {
        padding: 80px 0;
    }
    .category-card {
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    .stat-item {
        padding: 10px;
    }
    .search-form .input-group {
        border: 2px solid #dee2e6 !important;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .search-form .form-control,
    .search-form .form-select {
        border: none;
        padding: 15px 20px;
        background: transparent;
    }
    .search-form .form-control:focus,
    .search-form .form-select:focus {
        border: none;
        box-shadow: none;
        outline: none;
    }
    .search-form .btn {
        padding: 15px 30px;
    }
</style>
@endpush

