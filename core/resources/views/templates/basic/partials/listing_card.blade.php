<div class="col-md-6 col-lg-4">
    <div class="listing-card card h-100 shadow-sm border-0 overflow-hidden">
        <div class="card-img-top position-relative overflow-hidden">
            @if($listing->primaryImage)
                <a href="{{ route('marketplace.listing.show', $listing->slug) }}">
                    <img src="{{ asset('assets/images/default.png') }}"
                         data-src="{{ getImage(getFilePath('listing') . '/' . $listing->primaryImage->image) }}"
                         alt="{{ $listing->title }}" class="img-fluid listing-img lazy"
                         loading="lazy"
                         style="height: 200px; object-fit: cover; width: 100%; transition: transform 0.3s;">
                </a>
            @else
                @if($listing->business_type == 'domain' && $listing->domain_name)
                    @php
                        // Generate consistent color based on domain name
                        $domainName = $listing->domain_name ?? 'example.com';
                        $hash = 0;
                        for ($i = 0; $i < strlen($domainName); $i++) {
                            $hash = ord($domainName[$i]) + (($hash << 5) - $hash);
                        }
                        
                        // Predefined color gradients
                        $gradients = [
                            ['#667eea', '#764ba2'], // Purple
                            ['#f093fb', '#f5576c'], // Pink
                            ['#4facfe', '#00f2fe'], // Blue
                            ['#43e97b', '#38f9d7'], // Green
                            ['#fa709a', '#fee140'], // Pink-Yellow
                            ['#30cfd0', '#330867'], // Cyan-Purple
                            ['#a8edea', '#fed6e3'], // Light Blue-Pink
                            ['#ff9a9e', '#fecfef'], // Red-Pink
                            ['#ffecd2', '#fcb69f'], // Orange
                            ['#ff6e7f', '#bfe9ff'], // Red-Blue
                        ];
                        
                        // Ensure hash is positive and calculate safe index
                        $hash = abs($hash);
                        $gradientCount = count($gradients);
                        $index = $gradientCount > 0 ? ($hash % $gradientCount) : 0;
                        
                        // Double-check index is valid
                        if ($index < 0 || $index >= $gradientCount) {
                            $index = 0;
                        }
                        
                        $colors = $gradients[$index] ?? $gradients[0];
                    @endphp
                    <a href="{{ route('marketplace.listing.show', $listing->slug) }}" 
                       class="domain-card-image d-flex flex-column align-items-center justify-content-center position-relative text-decoration-none" 
                       style="height: 200px; background: linear-gradient(135deg, {{ $colors[0] }} 0%, {{ $colors[1] }} 100%);">
                        <div class="text-center text-white" style="z-index: 1;">
                            <i class="las la-globe mb-2" style="font-size: 3rem; opacity: 0.3;"></i>
                            <div class="position-relative">
                                <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 80px; height: 2px; background: rgba(255,255,255,0.5); transform: translateX(-50%);"></div>
                            </div>
                            <h3 class="mb-0 mt-3 fw-bold text-white" style="font-size: 1.75rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                {{ $listing->domain_name }}
                            </h3>
                        </div>
                    </a>
                @else
                    <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="placeholder-img bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        @if($listing->business_type == 'website')
                            <i class="las la-laptop text-muted" style="font-size: 4rem;"></i>
                        @elseif($listing->business_type == 'social_media_account')
                            <i class="las la-share-alt text-muted" style="font-size: 4rem;"></i>
                        @elseif($listing->business_type == 'mobile_app')
                            <i class="las la-mobile-alt text-muted" style="font-size: 4rem;"></i>
                        @else
                            <i class="las la-desktop text-muted" style="font-size: 4rem;"></i>
                        @endif
                    </a>
                @endif
            @endif
            
            <div class="listing-badges position-absolute top-0 start-0 end-0 p-2 d-flex justify-content-between">
                <div>
                    @if($listing->is_featured)
                        <span class="badge bg-warning text-dark shadow-sm">
                            <i class="las la-star"></i> @lang('Featured')
                        </span>
                    @endif
                    @if($listing->is_verified)
                        <span class="badge bg-success shadow-sm">
                            <i class="las la-check-circle"></i> @lang('Verified')
                        </span>
                    @endif
                </div>
                <div>
                    @if($listing->sale_type === 'auction')
                        <span class="badge bg-danger shadow-sm">
                            <i class="las la-gavel"></i> @lang('Auction')
                        </span>
                    @endif
                </div>
            </div>
            
            @if($listing->sale_type === 'auction' && $listing->auction_end)
                <div class="auction-timer position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2 text-center">
                    <small>
                        <i class="las la-clock"></i>
                        @if($listing->auction_end->isPast())
                            @lang('Auction Ended')
                        @else
                            @lang('Ends'): {{ $listing->auction_end->diffForHumans() }}
                        @endif
                    </small>
                </div>
            @endif
        </div>
        
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <a href="{{ route('marketplace.type', $listing->business_type) }}" class="badge bg--base text-white text-decoration-none shadow-sm">
                    @if($listing->business_type == 'domain')
                        <i class="las la-globe"></i>
                    @elseif($listing->business_type == 'website')
                        <i class="las la-laptop"></i>
                    @elseif($listing->business_type == 'social_media_account')
                        <i class="las la-share-alt"></i>
                    @elseif($listing->business_type == 'mobile_app')
                        <i class="las la-mobile-alt"></i>
                    @else
                        <i class="las la-desktop"></i>
                    @endif
                    {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                </a>
                @if($listing->listingCategory)
                    <small class="text-muted">
                        <a href="{{ route('marketplace.category', $listing->listingCategory->slug) }}" class="text-muted text-decoration-none">
                            {{ $listing->listingCategory->name }}
                        </a>
                    </small>
                @endif
            </div>
            
            <h5 class="card-title mb-1 fw-semibold">
                <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="text-dark text-decoration-none stretched-link-title">
                    @if($listing->business_type == 'domain' && $listing->domain_name)
                        {{ $listing->domain_name }}
                    @elseif($listing->business_type == 'website' && $listing->url)
                        @php
                            $url = $listing->url;
                            if (preg_match('/^https?:\/\/(.+)$/i', $url, $matches)) {
                                $url = $matches[1];
                            }
                            $url = preg_replace('/^www\./i', '', $url);
                            $url = explode('/', $url)[0];
                        @endphp
                        {{ $url }}
                    @elseif($listing->business_type == 'social_media_account' && $listing->url)
                        @php
                            $socialUrl = $listing->url;
                            $username = '';
                            if (preg_match('/(?:instagram|twitter|x|facebook|youtube|tiktok)\.com\/(?:@)?([^\/\?]+)/i', $socialUrl, $matches)) {
                                $username = '@' . $matches[1];
                            } else {
                                $username = $socialUrl;
                            }
                        @endphp
                        {{ $username }}
                    @else
                        {{ Str::limit($listing->title, 50) }}
                    @endif
                </a>
            </h5>
            
            @if($listing->tagline)
                <p class="card-text text-muted small mb-2">{{ Str::limit($listing->tagline, 80) }}</p>
            @endif

            {{-- Metrics --}}
            <div class="listing-stats d-flex flex-wrap gap-1 mb-3">
                @if($listing->monthly_revenue > 0)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                        <i class="las la-chart-line"></i> {{ gs('cur_sym') }}{{ shortNumber($listing->monthly_revenue) }}/mo
                    </span>
                @endif
                @if($listing->monthly_visitors > 0)
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                        <i class="las la-users"></i> {{ shortNumber($listing->monthly_visitors) }}
                    </span>
                @endif
                @if($listing->business_type === 'website' && $listing->business_location)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                        <i class="las la-map-marker"></i> {{ Str::limit($listing->business_location, 18) }}
                    </span>
                @endif
                @if($listing->business_type === 'website' && !is_null($listing->site_age_months) && (int)$listing->site_age_months > 0)
                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25">
                        <i class="las la-calendar"></i> {{ (int)$listing->site_age_months }}m
                    </span>
                @endif
                @if($listing->business_type === 'website' && !is_null($listing->overall_churn_percent))
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                        <i class="las la-percent"></i> {{ rtrim(rtrim(number_format((float)$listing->overall_churn_percent, 2), '0'), '.') }}%
                    </span>
                @endif
                @if($listing->followers_count > 0)
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                        <i class="las la-user-friends"></i> {{ shortNumber($listing->followers_count) }}
                    </span>
                @endif
                @if($listing->downloads_count > 0)
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                        <i class="las la-download"></i> {{ shortNumber($listing->downloads_count) }}
                    </span>
                @endif
            </div>
            
            {{-- Price Section --}}
            <div class="price-section pt-3 border-top">
                @if($listing->sale_type === 'auction')
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">@lang('Current Bid')</small>
                            <strong class="text--base fs-5">
                                {{ $listing->current_bid > 0 ? showAmount($listing->current_bid) : showAmount($listing->starting_bid) }}
                            </strong>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">{{ $listing->total_bids }} @lang('bids')</small>
                            @if($listing->buy_now_price > 0)
                                <small class="text-success">
                                    <i class="las la-bolt"></i> @lang('Buy Now'): {{ showAmount($listing->buy_now_price) }}
                                </small>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">@lang('Asking Price')</small>
                            <strong class="text--base fs-5">{{ showAmount($listing->asking_price) }}</strong>
                        </div>
                        @if($listing->monthly_profit > 0)
                            <div class="text-end">
                                <small class="text-muted d-block">@lang('Multiple')</small>
                                <span class="text-success fw-semibold">
                                    {{ number_format($listing->asking_price / ($listing->monthly_profit * 12), 1) }}x
                                </span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card-footer bg-light border-top py-2">
            @if(!empty($showPaybackButton))
                <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="btn btn--base btn-sm w-100 mb-2">
                    <i class="las la-calculator me-1"></i> @lang('See payback')
                </a>
            @endif
            <div class="d-flex justify-content-between align-items-center">
            <div class="seller-info d-flex align-items-center">
                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2" 
                     style="width: 28px; height: 28px; background: var(--base-color); opacity: 0.8;">
                    <span class="text-white small fw-bold">{{ strtoupper(substr($listing->seller->username ?? 'U', 0, 1)) }}</span>
                </div>
                <small>
                    <a href="{{ route('marketplace.seller', ['username' => $listing->seller->username ?? 'unknown']) }}" class="text-muted text-decoration-none">
                        {{ $listing->seller->username ?? 'Unknown' }}
                    </a>
                    @if($listing->seller && $listing->seller->is_verified_seller)
                        <i class="las la-check-circle text-primary" title="@lang('Verified Seller')"></i>
                    @endif
                </small>
            </div>
            <div class="listing-meta d-flex gap-3">
                <small class="text-muted" title="@lang('Views')">
                    <i class="las la-eye"></i> {{ shortNumber($listing->view_count) }}
                </small>
                <small class="text-muted" title="@lang('Watching')">
                    <i class="las la-heart"></i> {{ shortNumber($listing->watchlist_count) }}
                </small>
            </div>
            </div>
        </div>
    </div>
</div>

@push('style-lib')
<style>
.listing-card:hover .listing-img {
    transform: scale(1.05);
}
.listing-card .stretched-link-title:hover {
    color: var(--base-color) !important;
}
</style>
@endpush

