@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section">
        <div class="container">
            {{-- Header --}}
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="section-title mb-2">@lang('Compare Listings')</h2>
                    <p class="text-muted">@lang('Select up to 3 listings to compare side-by-side and find the best investment')</p>
                </div>
            </div>

            {{-- Compare Slots --}}
            <div class="row g-3 mb-4" id="compareSlots">
                @for($i = 0; $i < 3; $i++)
                    <div class="col-lg-4 col-md-6">
                        <div class="compare-slot" data-slot="{{ $i }}">
                            <div class="compare-slot-empty" id="emptySlot{{ $i }}">
                                <div class="slot-placeholder">
                                    <i class="las la-plus-circle"></i>
                                    <span>@lang('Add Listing')</span>
                                    <small class="text-muted">@lang('Click to search')</small>
                                </div>
                            </div>
                            <div class="compare-slot-filled d-none" id="filledSlot{{ $i }}">
                                {{-- Filled by JavaScript --}}
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Comparison Table --}}
            <div class="comparison-section d-none" id="comparisonSection">
                {{-- Value Score Header --}}
                <div class="card custom--card mb-4 comparison-header-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-3">
                                <h5 class="mb-0"><i class="las la-trophy text-warning me-2"></i>@lang('Value Score')</h5>
                                <small class="text-muted">@lang('Overall investment rating')</small>
                            </div>
                            <div class="col-lg-9">
                                <div class="row g-3" id="valueScoreRow">
                                    {{-- Filled by JavaScript --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detailed Comparison --}}
                <div class="card custom--card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">
                            <i class="las la-clipboard-list text-primary me-2"></i>
                            @lang('Detailed Comparison')
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="comparison-table" id="comparisonTable">
                            {{-- Pricing Section --}}
                            <div class="comparison-group">
                                <div class="comparison-group-header">
                                    <i class="las la-tag"></i> @lang('Pricing')
                                </div>
                                <div class="comparison-row" data-metric="price">
                                    <div class="metric-label">@lang('Current Price')</div>
                                    <div class="metric-values" id="metricPrice"></div>
                                </div>
                                <div class="comparison-row" data-metric="profit_multiple">
                                    <div class="metric-label">
                                        @lang('Profit Multiple')
                                        <span class="metric-hint" data-bs-toggle="tooltip" title="@lang('Price ÷ Annual Profit. Lower is better.')">
                                            <i class="las la-info-circle"></i>
                                        </span>
                                    </div>
                                    <div class="metric-values" id="metricProfitMultiple"></div>
                                </div>
                                <div class="comparison-row" data-metric="revenue_multiple">
                                    <div class="metric-label">
                                        @lang('Revenue Multiple')
                                        <span class="metric-hint" data-bs-toggle="tooltip" title="@lang('Price ÷ Annual Revenue')">
                                            <i class="las la-info-circle"></i>
                                        </span>
                                    </div>
                                    <div class="metric-values" id="metricRevenueMultiple"></div>
                                </div>
                            </div>

                            {{-- Financials Section --}}
                            <div class="comparison-group">
                                <div class="comparison-group-header">
                                    <i class="las la-chart-line"></i> @lang('Financials')
                                </div>
                                <div class="comparison-row" data-metric="monthly_revenue">
                                    <div class="metric-label">@lang('Monthly Revenue')</div>
                                    <div class="metric-values" id="metricMonthlyRevenue"></div>
                                </div>
                                <div class="comparison-row" data-metric="monthly_profit">
                                    <div class="metric-label">@lang('Monthly Profit')</div>
                                    <div class="metric-values" id="metricMonthlyProfit"></div>
                                </div>
                                <div class="comparison-row" data-metric="yearly_profit">
                                    <div class="metric-label">@lang('Annual Profit')</div>
                                    <div class="metric-values" id="metricYearlyProfit"></div>
                                </div>
                                <div class="comparison-row" data-metric="profit_margin">
                                    <div class="metric-label">
                                        @lang('Profit Margin')
                                        <span class="metric-hint" data-bs-toggle="tooltip" title="@lang('Profit ÷ Revenue × 100. Higher is better.')">
                                            <i class="las la-info-circle"></i>
                                        </span>
                                    </div>
                                    <div class="metric-values" id="metricProfitMargin"></div>
                                </div>
                            </div>

                            {{-- Business Details Section --}}
                            <div class="comparison-group">
                                <div class="comparison-group-header">
                                    <i class="las la-building"></i> @lang('Business Details')
                                </div>
                                <div class="comparison-row" data-metric="business_type">
                                    <div class="metric-label">@lang('Business Type')</div>
                                    <div class="metric-values" id="metricBusinessType"></div>
                                </div>
                                <div class="comparison-row" data-metric="category">
                                    <div class="metric-label">@lang('Category')</div>
                                    <div class="metric-values" id="metricCategory"></div>
                                </div>
                                <div class="comparison-row" data-metric="sale_type">
                                    <div class="metric-label">@lang('Sale Type')</div>
                                    <div class="metric-values" id="metricSaleType"></div>
                                </div>
                                <div class="comparison-row" data-metric="niche">
                                    <div class="metric-label">@lang('Niche')</div>
                                    <div class="metric-values" id="metricNiche"></div>
                                </div>
                            </div>

                            {{-- Trust & Verification Section --}}
                            <div class="comparison-group">
                                <div class="comparison-group-header">
                                    <i class="las la-shield-alt"></i> @lang('Trust & Verification')
                                </div>
                                <div class="comparison-row" data-metric="is_verified">
                                    <div class="metric-label">@lang('Verified Listing')</div>
                                    <div class="metric-values" id="metricIsVerified"></div>
                                </div>
                                <div class="comparison-row" data-metric="revenue_verified">
                                    <div class="metric-label">@lang('Revenue Verified')</div>
                                    <div class="metric-values" id="metricRevenueVerified"></div>
                                </div>
                                <div class="comparison-row" data-metric="traffic_verified">
                                    <div class="metric-label">@lang('Traffic Verified')</div>
                                    <div class="metric-values" id="metricTrafficVerified"></div>
                                </div>
                                <div class="comparison-row" data-metric="is_featured">
                                    <div class="metric-label">@lang('Featured')</div>
                                    <div class="metric-values" id="metricIsFeatured"></div>
                                </div>
                            </div>

                            {{-- Engagement Section --}}
                            <div class="comparison-group">
                                <div class="comparison-group-header">
                                    <i class="las la-eye"></i> @lang('Engagement')
                                </div>
                                <div class="comparison-row" data-metric="view_count">
                                    <div class="metric-label">@lang('View Count')</div>
                                    <div class="metric-values" id="metricViewCount"></div>
                                </div>
                                <div class="comparison-row" data-metric="created_at">
                                    <div class="metric-label">@lang('Listed Date')</div>
                                    <div class="metric-values" id="metricCreatedAt"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recommendation --}}
                <div class="card custom--card mt-4 recommendation-card d-none" id="recommendationCard">
                    <div class="card-body text-center py-4">
                        <div class="recommendation-icon mb-3">
                            <i class="las la-award"></i>
                        </div>
                        <h4 class="mb-2">@lang('Our Recommendation')</h4>
                        <p class="text-muted mb-3" id="recommendationText"></p>
                        <a href="#" class="btn btn--base btn--lg" id="recommendationLink">
                            <i class="las la-external-link-alt me-2"></i>@lang('View Listing')
                        </a>
                    </div>
                </div>
            </div>

            {{-- Search Panel (Modal-style) --}}
            <div class="search-panel-overlay d-none" id="searchPanelOverlay"></div>
            <div class="search-panel d-none" id="searchPanel">
                <div class="search-panel-header">
                    <h5 class="mb-0">@lang('Search Listings')</h5>
                    <button type="button" class="btn-close" id="closeSearchPanel"></button>
                </div>
                <div class="search-panel-body">
                    <div class="search-filters mb-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <input type="text" class="form-control form--control" id="searchQuery" 
                                       placeholder="@lang('Search by title, domain, or listing number...')">
                            </div>
                            <div class="col-md-4">
                                <select class="form-control form--control" id="filterCategory">
                                    <option value="">@lang('All Categories')</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control form--control" id="filterBusinessType">
                                    <option value="">@lang('All Types')</option>
                                    @foreach($businessTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control form--control" id="filterPriceRange">
                                    <option value="">@lang('Any Price')</option>
                                    <option value="0-5000">@lang('Under') {{ gs('cur_sym') }}5,000</option>
                                    <option value="5000-25000">{{ gs('cur_sym') }}5,000 - {{ gs('cur_sym') }}25,000</option>
                                    <option value="25000-100000">{{ gs('cur_sym') }}25,000 - {{ gs('cur_sym') }}100,000</option>
                                    <option value="100000-500000">{{ gs('cur_sym') }}100,000 - {{ gs('cur_sym') }}500,000</option>
                                    <option value="500000-">{{ gs('cur_sym') }}500,000+</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-results" id="searchResults">
                        <div class="text-center text-muted py-4">
                            <i class="las la-search la-3x mb-2"></i>
                            <p>@lang('Enter a search term or apply filters to find listings')</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Suggested Listings --}}
            @if($suggestedListings->count() > 0)
            <div class="suggested-section mt-5" id="suggestedSection">
                <h5 class="mb-3">
                    <i class="las la-lightbulb text-warning me-2"></i>
                    @lang('Popular Listings to Compare')
                </h5>
                <div class="row g-3">
                    @foreach($suggestedListings as $listing)
                        <div class="col-lg-4 col-md-6">
                            <div class="suggested-listing-card" data-listing="{{ json_encode($listing) }}">
                                <div class="suggested-listing-image">
                                    <img src="{{ $listing['image'] }}" alt="{{ $listing['title'] }}">
                                    @if($listing['is_featured'])
                                        <span class="featured-badge"><i class="las la-star"></i></span>
                                    @endif
                                </div>
                                <div class="suggested-listing-info">
                                    <h6 class="listing-title">{{ Str::limit($listing['title'], 40) }}</h6>
                                    <div class="listing-meta">
                                        <span class="listing-price">{{ gs('cur_sym') }}{{ number_format($listing['price']) }}</span>
                                        <span class="listing-category">{{ $listing['category'] }}</span>
                                    </div>
                                    <div class="listing-stats">
                                        <span><i class="las la-chart-line"></i> {{ gs('cur_sym') }}{{ number_format($listing['monthly_profit']) }}/mo</span>
                                        <span><i class="las la-eye"></i> {{ number_format($listing['view_count']) }}</span>
                                    </div>
                                </div>
                                <button class="btn btn--base btn-sm add-to-compare-btn w-100 mt-2">
                                    <i class="las la-plus me-1"></i>@lang('Add to Compare')
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- CTA Banner --}}
            <div class="row mt-5">
                <div class="col-12">
                    <div class="cta-banner">
                        <div class="row align-items-center">
                            <div class="col-lg-8 mb-3 mb-lg-0">
                                <h4 class="text-white mb-2">@lang('Found the Perfect Business?')</h4>
                                <p class="text-white-50 mb-0">
                                    @lang('Create an account to contact sellers, place bids, and start your acquisition journey.')
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <a href="{{ route('user.register') }}" class="btn btn-light btn--lg">
                                    <i class="las la-user-plus me-2"></i>
                                    @lang('Sign Up Free')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
<style>
    /* Compare Slots */
    .compare-slot {
        height: 280px;
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .compare-slot:hover {
        border-color: hsl(var(--base));
    }
    .compare-slot.has-listing {
        border-style: solid;
        border-color: hsl(var(--base));
    }
    .compare-slot-empty {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .compare-slot-empty:hover {
        background: linear-gradient(135deg, hsl(var(--base)/0.05) 0%, hsl(var(--base)/0.1) 100%);
    }
    .slot-placeholder {
        text-align: center;
    }
    .slot-placeholder i {
        font-size: 3rem;
        color: hsl(var(--base));
        margin-bottom: 10px;
        display: block;
    }
    .slot-placeholder span {
        display: block;
        font-weight: 600;
        color: #495057;
    }

    /* Filled Slot */
    .compare-slot-filled {
        height: 100%;
        padding: 15px;
        position: relative;
    }
    .filled-slot-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }
    .filled-slot-image {
        width: 70px;
        height: 70px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .filled-slot-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .filled-slot-info {
        flex: 1;
        min-width: 0;
    }
    .filled-slot-title {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .filled-slot-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: hsl(var(--base));
    }
    .filled-slot-meta {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .filled-slot-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 10px;
    }
    .slot-stat {
        background: #f8f9fa;
        padding: 8px;
        border-radius: 6px;
        text-align: center;
    }
    .slot-stat-value {
        font-weight: 600;
        font-size: 0.9rem;
        display: block;
    }
    .slot-stat-label {
        font-size: 0.7rem;
        color: #6c757d;
    }
    .remove-listing-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        background: #dc3545;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .remove-listing-btn:hover {
        background: #c82333;
        transform: scale(1.1);
    }

    /* Search Panel */
    .search-panel-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
    }
    .search-panel {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        z-index: 1050;
        display: flex;
        flex-direction: column;
    }
    .search-panel-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .search-panel-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }
    .search-results {
        max-height: 400px;
        overflow-y: auto;
    }
    .search-result-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .search-result-item:hover {
        background: #f8f9fa;
        border-color: hsl(var(--base));
    }
    .search-result-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .search-result-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .search-result-info {
        flex: 1;
        min-width: 0;
    }
    .search-result-title {
        font-weight: 600;
        margin-bottom: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .search-result-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .search-result-price {
        font-weight: 700;
        color: hsl(var(--base));
        white-space: nowrap;
    }

    /* Comparison Table */
    .comparison-group {
        border-bottom: 1px solid #e9ecef;
    }
    .comparison-group:last-child {
        border-bottom: none;
    }
    .comparison-group-header {
        background: #f8f9fa;
        padding: 12px 20px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #495057;
    }
    .comparison-group-header i {
        margin-right: 8px;
        color: hsl(var(--base));
    }
    .comparison-row {
        display: flex;
        border-bottom: 1px solid #f0f0f0;
    }
    .comparison-row:last-child {
        border-bottom: none;
    }
    .metric-label {
        width: 200px;
        padding: 14px 20px;
        font-weight: 500;
        background: #fafafa;
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }
    .metric-hint {
        color: #adb5bd;
        cursor: help;
    }
    .metric-values {
        flex: 1;
        display: flex;
    }
    .metric-value {
        flex: 1;
        padding: 14px 20px;
        text-align: center;
        border-left: 1px solid #f0f0f0;
        transition: background 0.2s;
    }
    .metric-value.best {
        background: #d4edda;
        font-weight: 600;
    }
    .metric-value.worst {
        background: #fff3cd;
    }
    .metric-value .badge {
        font-size: 0.75rem;
    }

    /* Value Score */
    .value-score-card {
        text-align: center;
        padding: 15px;
        border-radius: 10px;
        background: #f8f9fa;
        position: relative;
    }
    .value-score-card.best-score {
        background: linear-gradient(135deg, hsl(var(--base)/0.1) 0%, hsl(var(--base)/0.2) 100%);
        border: 2px solid hsl(var(--base));
    }
    .value-score-card.best-score::after {
        content: '★ BEST VALUE';
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: hsl(var(--base));
        color: #fff;
        font-size: 0.65rem;
        padding: 2px 10px;
        border-radius: 10px;
        font-weight: 600;
    }
    .score-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.5rem;
        font-weight: 700;
    }
    .score-excellent { background: #d4edda; color: #155724; }
    .score-good { background: #cce5ff; color: #004085; }
    .score-fair { background: #fff3cd; color: #856404; }
    .score-poor { background: #f8d7da; color: #721c24; }
    .score-title {
        font-weight: 600;
        font-size: 0.85rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Suggested Listings */
    .suggested-listing-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .suggested-listing-card:hover {
        border-color: hsl(var(--base));
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .suggested-listing-image {
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 12px;
        position: relative;
    }
    .suggested-listing-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .featured-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #ffc107, #ff9800);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }
    .listing-title {
        font-size: 0.95rem;
        margin-bottom: 8px;
    }
    .listing-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .listing-price {
        font-weight: 700;
        color: hsl(var(--base));
    }
    .listing-category {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .listing-stats {
        display: flex;
        gap: 15px;
        font-size: 0.8rem;
        color: #6c757d;
    }
    .listing-stats i {
        margin-right: 3px;
    }

    /* Recommendation Card */
    .recommendation-card {
        background: linear-gradient(135deg, hsl(var(--base)/0.05) 0%, hsl(var(--base)/0.1) 100%);
        border: 2px solid hsl(var(--base));
    }
    .recommendation-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: hsl(var(--base));
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .recommendation-icon i {
        font-size: 2.5rem;
        color: #fff;
    }

    /* CTA Banner */
    .cta-banner {
        background: linear-gradient(135deg, hsl(var(--base)) 0%, hsl(var(--base)/0.8) 100%);
        padding: 40px;
        border-radius: 16px;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .compare-slot {
            height: 200px;
        }
        .metric-label {
            width: 150px;
            font-size: 0.85rem;
            padding: 10px 15px;
        }
        .metric-value {
            padding: 10px;
            font-size: 0.85rem;
        }
    }
    @media (max-width: 767px) {
        .search-panel {
            width: 95%;
            max-height: 90vh;
        }
        .comparison-row {
            flex-direction: column;
        }
        .metric-label {
            width: 100%;
        }
        .metric-values {
            flex-wrap: wrap;
        }
        .metric-value {
            flex: 1 1 50%;
            min-width: 50%;
        }
    }
</style>
@endpush

@push('script')
    <script>
        const currencySymbol = '{{ gs("cur_sym") }}';
        const preloadedListings = @json($preloadedListings);
        const searchUrl = '{{ route("tools.compare.search") }}';
        const listingUrl = '{{ route("tools.compare.listing", ":id") }}';
    </script>
    <script src="{{ asset($activeTemplateTrue . 'js/listing-compare.js') }}?v={{ time() }}"></script>
@endpush
