@extends($activeTemplate . 'layouts.frontend')

@push('meta')
@php
    $seo = gs();
    $metaDescription = $seoContents->description ?? ($listing->tagline ?? strip_tags(Str::limit($listing->description, 160)));
    $metaImage = $seoImage ?? ($listing->primaryImage ? getImage(getFilePath('listing') . '/' . $listing->primaryImage->image) : getImage(getFilePath('seo') . '/' . gs('seo_image')));
    $metaTitle = $listing->title . ' - ' . gs('site_name');
    $listingUrl = route('marketplace.listing.show', $listing->slug);
    $price = $listing->sale_type === 'auction' 
        ? ($listing->current_bid > 0 ? $listing->current_bid : $listing->starting_bid)
        : $listing->asking_price;
@endphp
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ implode(',', $seoContents->keywords ?? []) }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="product">
<meta property="og:url" content="{{ $listingUrl }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="product:price:amount" content="{{ $price }}">
<meta property="product:price:currency" content="{{ strtoupper(gs('cur_text')) }}">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $listingUrl }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">

<!-- Schema.org structured data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "{{ $listing->title }}",
  "description": "{{ $metaDescription }}",
  "image": "{{ $metaImage }}",
  "offers": {
    "@type": "Offer",
    "url": "{{ $listingUrl }}",
    "priceCurrency": "{{ strtoupper(gs('cur_text')) }}",
    "price": "{{ $price }}",
    "availability": "https://schema.org/{{ $listing->status === \App\Constants\Status::LISTING_ACTIVE ? 'InStock' : 'OutOfStock' }}",
    "seller": {
      "@type": "Person",
      "name": "{{ $listing->seller->username ?? 'Unknown' }}"
    }
  },
  "brand": {
    "@type": "Brand",
    "name": "{{ $listing->domain_name ?? $listing->title }}"
  },
  "category": "{{ $listing->listingCategory->name ?? ucfirst($listing->business_type) }}",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ $listing->seller->avg_rating ?? 0 }}",
    "reviewCount": "{{ $listing->seller->total_reviews ?? 0 }}"
  }
}
</script>

{{-- BreadcrumbList schema for rich search results --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "{{ url('/') }}" },
    { "@type": "ListItem", "position": 2, "name": "Marketplace", "item": "{{ route('marketplace.index') }}" }
    @if($listing->listingCategory)
    ,{ "@type": "ListItem", "position": 3, "name": "{{ $listing->listingCategory->name }}", "item": "{{ route('marketplace.category', $listing->listingCategory->slug) }}" },
    { "@type": "ListItem", "position": 4, "name": "{{ $listing->title }}", "item": "{{ $listingUrl }}" }
    @else
    ,{ "@type": "ListItem", "position": 3, "name": "{{ $listing->title }}", "item": "{{ $listingUrl }}" }
    @endif
  ]
}
</script>
@endpush

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Listing Header -->
                <div class="listing-header mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-secondary mb-2">
                                {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                            </span>
                            @if($listing->is_verified)
                                <span class="badge bg-success mb-2">
                                    <i class="las la-check-circle"></i> @lang('Verified')
                                </span>
                            @endif
                            @if($listing->is_featured)
                                <span class="badge bg-warning mb-2">
                                    <i class="las la-star"></i> @lang('Featured')
                                </span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @auth
                                <form action="{{ route('user.watchlist.toggle', $listing->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="las la-heart{{ $isWatching ? ' text-danger' : '' }}"></i>
                                        {{ $isWatching ? __('Watching') : __('Watch') }}
                                    </button>
                                </form>
                            @endauth
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="las la-share"></i> @lang('Share')
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="shareUrl(); return false;"><i class="las la-share me-2"></i>@lang('Share via device')</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="copyListingLink(); return false;"><i class="las la-link me-2"></i>@lang('Copy link')</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="https://twitter.com/intent/tweet?text={{ urlencode($listing->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer"><i class="lab la-twitter me-2"></i>Twitter</a></li>
                                    <li><a class="dropdown-item" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer"><i class="lab la-facebook-f me-2"></i>Facebook</a></li>
                                    <li><a class="dropdown-item" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer"><i class="lab la-linkedin-in me-2"></i>LinkedIn</a></li>
                                </ul>
                            </div>
                            @auth
                                @if($listing->user_id != auth()->id())
                                    <a href="{{ route('user.report.create', ['type' => 'listing', 'id' => $listing->id]) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="las la-flag"></i> @lang('Report')
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                    
                    <h1 class="h2 mb-2">{{ $listing->title }}</h1>
                    @if($listing->tagline)
                        <p class="lead text-muted">{{ $listing->tagline }}</p>
                    @endif
                    
                    <div class="listing-meta text-muted small">
                        <span><i class="las la-eye"></i> {{ number_format($listing->view_count) }} @lang('views')</span>
                        <span class="mx-2">|</span>
                        <span><i class="las la-heart"></i> {{ number_format($listing->watchlist_count) }} @lang('watchers')</span>
                        <span class="mx-2">|</span>
                        <span>@lang('Listed') {{ $listing->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                
                <!-- Images -->
                @if($listing->business_type !== 'domain' && $listing->images->count() > 0)
                <div class="listing-images mb-4">
                    @if($listing->images->count() === 1)
                        <!-- Single Image - Simple Display -->
                        <div class="main-image-container mb-3 position-relative">
                            <img src="{{ getImage(getFilePath('listing') . '/' . $listing->images->first()->image) }}"
                                 alt="{{ $listing->title }}"
                                 class="img-fluid rounded w-100 main-image"
                                 id="mainImage"
                                 style="cursor: pointer;"
                                 onclick="openFullscreenGallery(0)"
                                 onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">
                        </div>
                    @else
                        <!-- Multiple Images - Gallery Display -->
                        <div class="main-image-container mb-3 position-relative">
                            <img src="{{ getImage(getFilePath('listing') . '/' . $listing->images->first()->image) }}"
                                 alt="{{ $listing->title }}"
                                 class="img-fluid rounded w-100 main-image"
                                 id="mainImage"
                                 style="cursor: pointer;"
                                 onclick="openFullscreenGallery(currentIndex)"
                                 onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">

                            <!-- Navigation arrows -->
                            <button class="btn btn-dark btn-sm position-absolute top-50 start-0 translate-middle-y ms-2 nav-arrow prev-arrow"
                                    id="prevImage" style="display: none; opacity: 0.7;">
                                <i class="las la-chevron-left"></i>
                            </button>
                            <button class="btn btn-dark btn-sm position-absolute top-50 end-0 translate-middle-y me-2 nav-arrow next-arrow"
                                    id="nextImage" style="opacity: 0.7;">
                                <i class="las la-chevron-right"></i>
                            </button>

                            <!-- Image counter -->
                            <div class="position-absolute bottom-0 end-0 mb-2 me-2">
                                <span class="badge bg-dark bg-opacity-75 text-white px-2 py-1">
                                    <span id="currentImageIndex">1</span> / {{ $listing->images->count() }}
                                </span>
                            </div>
                        </div>

                        <!-- Thumbnail Gallery -->
                        <div class="thumbnail-container">
                            <div class="thumbnail-images d-flex gap-2 overflow-auto pb-2" id="thumbnailStrip">
                                @foreach($listing->images as $index => $image)
                                    @php
                                        $thumbPath = getFilePath('listing') . '/' . $image->image;
                                        $thumbUrl = getImage($thumbPath);
                                    @endphp
                                    <div class="thumbnail-wrapper position-relative" data-index="{{ $index }}">
                                        <img src="{{ $thumbUrl }}"
                                             alt="{{ $listing->title }}"
                                             class="img-thumbnail thumbnail-item {{ $index === 0 ? 'active' : '' }}"
                                             style="width: 80px; height: 60px; object-fit: cover; cursor: pointer; border: 2px solid {{ $index === 0 ? '#007bff' : 'transparent' }}; transition: all 0.3s ease;"
                                             data-index="{{ $index }}"
                                             onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">

                                        <!-- Active indicator -->
                                        @if($index === 0)
                                        <div class="position-absolute top-0 end-0 mt-1 me-1">
                                            <i class="las la-check-circle text-primary" style="font-size: 14px;"></i>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Thumbnail scroll indicators -->
                            @if($listing->images->count() > 6)
                            <div class="thumbnail-scroll-hint text-center mt-2">
                                <small class="text-muted">
                                    <i class="las la-arrows-alt-h"></i> Scroll for more images
                                </small>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
                @elseif($listing->business_type === 'domain')
                <!-- Domain preview with gradient background -->
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
                <div class="domain-preview mb-4 rounded overflow-hidden position-relative" 
                     style="height: 400px; background: linear-gradient(135deg, {{ $colors[0] }} 0%, {{ $colors[1] }} 100%);">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center text-white" style="z-index: 1;">
                        <i class="las la-globe mb-3" style="font-size: 5rem; opacity: 0.3;"></i>
                        <div class="position-relative">
                            <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 100px; height: 2px; background: rgba(255,255,255,0.5); transform: translateX(-50%);"></div>
                        </div>
                        <h2 class="mb-0 mt-4 fw-bold text-white" style="font-size: 2.5rem; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                            {{ $listing->domain_name ?? $listing->title }}
                        </h2>
                    </div>
                </div>
                @endif
                
                <!-- Description -->
                <div class="listing-description card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Description')</h4>
                        <div class="description-content">
                            {!! nl2br(e($listing->description)) !!}
                        </div>
                    </div>
                </div>
                
                <!-- Business Details -->
                <div class="business-details card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Business Details')</h4>
                        <div class="row g-3">
                            @if($listing->business_type === 'domain')
                                @if($listing->domain_name)
                                    <div class="col-md-6">
                                        <strong>@lang('Domain'):</strong> {{ $listing->domain_name }}
                                    </div>
                                @endif
                                @if($listing->domain_registrar)
                                    <div class="col-md-6">
                                        <strong>@lang('Registrar'):</strong> {{ $listing->domain_registrar }}
                                    </div>
                                @endif
                                @if($listing->domain_expiry)
                                    <div class="col-md-6">
                                        <strong>@lang('Expires'):</strong> {{ $listing->domain_expiry->format('M d, Y') }}
                                    </div>
                                @endif
                                @if($listing->domain_age_years)
                                    <div class="col-md-6">
                                        <strong>@lang('Age'):</strong> {{ $listing->domain_age_years }} @lang('years')
                                    </div>
                                @endif
                            @elseif($listing->business_type === 'website')
                                @if($listing->url)
                                    <div class="col-md-6">
                                        <strong>@lang('URL'):</strong> 
                                        <a href="{{ $listing->url }}" target="_blank" rel="nofollow">{{ $listing->url }}</a>
                                    </div>
                                @endif
                                @if($listing->niche)
                                    <div class="col-md-6">
                                        <strong>@lang('Niche'):</strong> {{ $listing->niche }}
                                    </div>
                                @endif
                                @if($listing->tech_stack)
                                    <div class="col-md-6">
                                        <strong>@lang('Tech Stack'):</strong> {{ $listing->tech_stack }}
                                    </div>
                                @endif
                                @if($listing->business_location)
                                    <div class="col-md-6">
                                        <strong>@lang('Business Location'):</strong> {{ $listing->business_location }}
                                    </div>
                                @endif
                                @if(!is_null($listing->overall_churn_percent))
                                    <div class="col-md-6">
                                        <strong>@lang('Overall Churn'):</strong> {{ rtrim(rtrim(number_format((float)$listing->overall_churn_percent, 2), '0'), '.') }}%
                                    </div>
                                @endif
                                @if(!is_null($listing->site_age_months))
                                    @php
                                        $siteAgeMonths = (int) $listing->site_age_months;
                                        $siteAgeYears = $siteAgeMonths > 0 ? floor($siteAgeMonths / 12) : 0;
                                    @endphp
                                    <div class="col-md-6">
                                        <strong>@lang('Site Age'):</strong>
                                        {{ number_format($siteAgeMonths) }} @lang('months')
                                        @if($siteAgeYears > 0)
                                            ({{ $siteAgeYears }} @lang('years'))
                                        @endif
                                    </div>
                                @endif
                            @elseif($listing->business_type === 'social_media_account')
                                @if($listing->platform)
                                    <div class="col-md-6">
                                        <strong>@lang('Platform'):</strong> {{ ucfirst($listing->platform) }}
                                    </div>
                                @endif
                                @if($listing->niche)
                                    <div class="col-md-6">
                                        <strong>@lang('Niche'):</strong> {{ $listing->niche }}
                                    </div>
                                @endif
                                @if($listing->followers_count)
                                    <div class="col-md-6">
                                        <strong>@lang('Followers'):</strong> {{ number_format($listing->followers_count) }}
                                    </div>
                                @endif
                                @if($listing->engagement_rate)
                                    <div class="col-md-6">
                                        <strong>@lang('Engagement Rate'):</strong> {{ $listing->engagement_rate }}%
                                    </div>
                                @endif
                            @elseif(in_array($listing->business_type, ['mobile_app', 'desktop_app']))
                                @if($listing->app_store_url)
                                    <div class="col-md-6">
                                        <strong>@lang('App Store'):</strong> 
                                        <a href="{{ $listing->app_store_url }}" target="_blank">@lang('View')</a>
                                    </div>
                                @endif
                                @if($listing->play_store_url)
                                    <div class="col-md-6">
                                        <strong>@lang('Play Store'):</strong> 
                                        <a href="{{ $listing->play_store_url }}" target="_blank">@lang('View')</a>
                                    </div>
                                @endif
                                @if($listing->downloads_count)
                                    <div class="col-md-6">
                                        <strong>@lang('Downloads'):</strong> {{ number_format($listing->downloads_count) }}
                                    </div>
                                @endif
                                @if($listing->app_rating)
                                    <div class="col-md-6">
                                        <strong>@lang('Rating'):</strong> {{ $listing->app_rating }}/5
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Monetization Methods (Website / Apps / Social) --}}
                        @if(in_array($listing->business_type, ['website','social_media_account','mobile_app','desktop_app']) && (!empty($listing->monetization_methods) || $listing->monetization_other))
                            @php
                                $labelMap = [
                                    'subscriptions' => 'Subscriptions / Membership',
                                    'ads' => 'Ads',
                                    'affiliate' => 'Affiliate',
                                    'ecommerce' => 'E-commerce / Products',
                                    'digital_downloads' => 'Digital downloads',
                                    'lead_gen' => 'Lead gen',
                                    'services' => 'Services / Consulting',
                                    'sponsorships' => 'Sponsorships / Brand sponsorships',
                                    'marketplace_commission' => 'Marketplace / Commission',
                                    'saas' => 'SaaS',
                                    'one_time_purchase' => 'One-time purchase',
                                    'in_app_purchases' => 'In-app purchases',
                                    'licensing' => 'Licensing / Enterprise',
                                    'support_contracts' => 'Services / Support contracts',
                                    'ad_revenue_share' => 'Ad revenue share',
                                    'sell_products' => 'Selling products',
                                    'sell_services' => 'Selling services',
                                    'donations' => 'Donations / Tips',
                                    'paid_membership' => 'Paid community / Membership',
                                    'other' => 'Other',
                                ];
                                $methods = is_array($listing->monetization_methods) ? $listing->monetization_methods : [];
                                $methods = array_values(array_unique(array_filter($methods)));
                            @endphp

                            <hr class="my-3">
                            <div>
                                <strong class="d-block mb-2 text-dark">@lang('Monetization Methods'):</strong>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($methods as $m)
                                        <span class="badge bg--base text-white">
                                            {{ __($labelMap[$m] ?? $m) }}
                                        </span>
                                    @endforeach
                                    @if($listing->monetization_other)
                                        <span class="badge bg--base text-white">
                                            @lang('Other'): {{ $listing->monetization_other }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Financials -->
                @if($listing->monthly_revenue > 0 || $listing->monthly_profit > 0)
                <div class="financials card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Financials')</h4>
                        <div class="row g-3">
                            @if($listing->monthly_revenue > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('MRR (Monthly Revenue)')</small>
                                        <strong class="fs-5">{{ showAmount($listing->monthly_revenue) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->monthly_profit > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Monthly Profit')</small>
                                        <strong class="fs-5">{{ showAmount($listing->monthly_profit) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->yearly_revenue > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Yearly Revenue')</small>
                                        <strong class="fs-5">{{ showAmount($listing->yearly_revenue) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->yearly_profit > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Yearly Profit')</small>
                                        <strong class="fs-5">{{ showAmount($listing->yearly_profit) }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Traffic -->
                @if($listing->monthly_visitors > 0 || $listing->monthly_page_views > 0)
                <div class="traffic card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Traffic')</h4>
                        <div class="row g-3">
                            @if($listing->monthly_visitors > 0)
                                <div class="col-md-4 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Monthly Visitors')</small>
                                        <strong class="fs-5">{{ number_format($listing->monthly_visitors) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->monthly_page_views > 0)
                                <div class="col-md-4 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Page Views')</small>
                                        <strong class="fs-5">{{ number_format($listing->monthly_page_views) }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Q&A Section -->
                <div class="qa-section card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Questions & Answers')</h4>
                        
                        @if($listing->questions->count() > 0)
                            <div class="questions-list mb-4">
                                @foreach($listing->questions as $question)
                                    <div class="question-item border-bottom pb-3 mb-3">
                                        <div class="question">
                                            <strong><i class="las la-question-circle text--base"></i> {{ $question->question }}</strong>
                                            <small class="text-muted d-block">
                                                @lang('Asked by') {{ $question->asker->username ?? 'Anonymous' }} 
                                                {{ $question->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        @if($question->answer)
                                            <div class="answer mt-2 ps-4">
                                                <i class="las la-reply text-muted"></i> {{ $question->answer }}
                                                <small class="text-muted d-block">
                                                    @lang('Answered') {{ $question->answered_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">@lang('No questions yet. Be the first to ask!')</p>
                        @endif
                        
                        @auth
                            @if(auth()->id() !== $listing->user_id && $listing->status == \App\Constants\Status::LISTING_ACTIVE)
                                <form action="{{ route('marketplace.listing.question', $listing->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea name="question" class="form-control" rows="2" 
                                                  placeholder="@lang('Ask the seller a question...')" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn--base btn-sm">
                                        <i class="las la-paper-plane"></i> @lang('Ask Question')
                                    </button>
                                </form>
                            @endif
                        @else
                            <p class="text-muted">
                                <a href="{{ route('user.login', ['redirect' => route('marketplace.listing.show', $listing->slug)]) }}">@lang('Login')</a> @lang('to ask a question')
                            </p>
                        @endauth
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Price Card -->
                <div class="price-card card shadow-sm mb-4">
                    <div class="card-body">
                        @if($listing->sale_type === 'auction')
                            <div class="auction-info">
                                <div class="current-bid mb-3">
                                    <small class="text-muted d-block">@lang('Current Bid')</small>
                                    <span class="fs-2 fw-bold text--base">
                                        {{ $listing->current_bid > 0 ? showAmount($listing->current_bid) : showAmount($listing->starting_bid) }}
                                    </span>
                                </div>
                                
                                <div class="auction-timer mb-3 p-3 bg-light rounded">
                                    <small class="text-muted d-block">@lang('Time Remaining')</small>
                                    @if($listing->auction_end && $listing->auction_end->isFuture())
                                        <span class="fs-5 text-danger" id="countdownTimer">
                                            {{ $listing->auction_end->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="fs-5 text-muted">@lang('Auction Ended')</span>
                                    @endif
                                </div>
                                
                                <div class="bid-stats mb-3">
                                    <span class="me-3"><i class="las la-gavel"></i> {{ $listing->total_bids }} @lang('bids')</span>
                                    @if($listing->reserve_price > 0)
                                        @if($listing->hasReserveBeenMet())
                                            <span class="text-success"><i class="las la-check"></i> @lang('Reserve Met')</span>
                                        @else
                                            <span class="text-warning"><i class="las la-times"></i> @lang('Reserve Not Met')</span>
                                        @endif
                                    @endif
                                </div>
                                
                                @if($listing->status == \App\Constants\Status::LISTING_ACTIVE && $listing->auction_end && $listing->auction_end->isFuture())
                                    @auth
                                        @if(auth()->id() !== $listing->user_id)
                                            @if(isset($buyerFeeBreakdown) && ($buyerFeeBreakdown['is_bid_example'] ?? false))
                                                <div class="alert alert-info py-2 px-3 mb-3 small">
                                                    <strong>@lang('Fees at checkout'):</strong>
                                                    @lang('If you win, you will pay your bid amount plus any applicable service fee. Example for a') {{ showAmount($buyerFeeBreakdown['amount']) }} @lang('bid'): @lang('fee') {{ showAmount($buyerFeeBreakdown['buyer_fee']) }} — @lang('Total') {{ showAmount($buyerFeeBreakdown['total']) }}.
                                                </div>
                                            @endif
                                            <form action="{{ route('user.bid.place', $listing->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">@lang('Your Bid') (@lang('Min'): {{ showAmount($listing->minimum_bid) }})</label>
                                                    <input type="number" name="amount" class="form-control form-control-lg" 
                                                           min="{{ $listing->minimum_bid }}" step="0.01" required
                                                           placeholder="{{ showAmount($listing->minimum_bid, currencyFormat: false) }}">
                                                </div>
                                                <button type="submit" class="btn btn--base btn-lg w-100 mb-2">
                                                    <i class="las la-gavel"></i> @lang('Place Bid')
                                                </button>
                                            </form>
                                            
                                            @if($listing->buy_now_price > 0)
                                                @if(isset($buyerFeeBreakdown) && !($buyerFeeBreakdown['is_bid_example'] ?? false))
                                                    <div class="border rounded p-3 mb-3 bg-light">
                                                        <div class="small text-muted mb-1">@lang('What you pay if you buy now')</div>
                                                        <div class="d-flex justify-content-between"><span>@lang('Listing price'):</span> <strong>{{ showAmount($buyerFeeBreakdown['amount']) }}</strong></div>
                                                        @if(($buyerFeeBreakdown['buyer_fee'] ?? 0) > 0)
                                                            <div class="d-flex justify-content-between"><span>@lang('Service fee'):</span> <strong>{{ showAmount($buyerFeeBreakdown['buyer_fee']) }}</strong></div>
                                                            @if(!empty($buyerFeeBreakdown['fees']))
                                                                @foreach($buyerFeeBreakdown['fees'] as $fee)
                                                                    <div class="d-flex justify-content-between ps-2 small"><span>{{ $fee['name'] ?? 'Fee' }}:</span> {{ showAmount($fee['amount'] ?? 0) }}</div>
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                        <hr class="my-2">
                                                        <div class="d-flex justify-content-between fw-bold"><span>@lang('Total you pay'):</span> <span class="text--base">{{ showAmount($buyerFeeBreakdown['total']) }}</span></div>
                                                    </div>
                                                @endif
                                                <form action="{{ route('user.bid.buy.now', $listing->id) }}" method="POST">
                                                    @csrf
                                                    @php
                                                        $buyNowTotal = isset($buyerFeeBreakdown) && !($buyerFeeBreakdown['is_bid_example'] ?? false) ? $buyerFeeBreakdown['total'] : $listing->buy_now_price;
                                                    @endphp
                                                    <button type="submit" class="btn btn-outline-success btn-lg w-100" 
                                                            onclick="return confirm('@lang('Buy now: Listing price') {{ showAmount($listing->buy_now_price) }}. @lang('Total you pay') {{ showAmount($buyNowTotal) }}. @lang('Continue?')')">
                                                        <i class="las la-shopping-cart"></i> @lang('Buy Now') - {{ showAmount($listing->buy_now_price) }}
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <div class="alert alert-info">@lang('This is your listing')</div>
                                        @endif
                                    @else
                                        <a href="{{ route('user.login', ['redirect' => route('marketplace.listing.show', $listing->slug)]) }}" class="btn btn--base btn-lg w-100">
                                            @lang('Login to Bid')
                                        </a>
                                    @endauth
                                @endif
                            </div>
                        @else
                            <div class="fixed-price-info">
                                <div class="asking-price mb-3">
                                    <small class="text-muted d-block">@lang('Asking Price')</small>
                                    <span class="fs-2 fw-bold text--base">{{ showAmount($listing->asking_price) }}</span>
                                </div>
                                @if(isset($buyerFeeBreakdown) && ($buyerFeeBreakdown['buyer_fee'] ?? 0) > 0)
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <div class="small text-muted mb-1">@lang('What you pay (if offer accepted)')</div>
                                        <div class="d-flex justify-content-between"><span>@lang('Listing price'):</span> <strong>{{ showAmount($buyerFeeBreakdown['amount']) }}</strong></div>
                                        <div class="d-flex justify-content-between"><span>@lang('Service fee'):</span> <strong>{{ showAmount($buyerFeeBreakdown['buyer_fee']) }}</strong></div>
                                        @if(!empty($buyerFeeBreakdown['fees']))
                                            @foreach($buyerFeeBreakdown['fees'] as $fee)
                                                <div class="d-flex justify-content-between ps-2 small"><span>{{ $fee['name'] ?? 'Fee' }}:</span> {{ showAmount($fee['amount'] ?? 0) }}</div>
                                            @endforeach
                                        @endif
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between fw-bold"><span>@lang('Total you pay'):</span> <span class="text--base">{{ showAmount($buyerFeeBreakdown['total']) }}</span></div>
                                    </div>
                                @elseif(isset($buyerFeeBreakdown))
                                    <div class="small mb-2">@lang('Total you pay'): <strong>{{ showAmount($buyerFeeBreakdown['total']) }}</strong> @lang('(no additional fees)')</div>
                                @endif
                                
                                @if($listing->status == \App\Constants\Status::LISTING_ACTIVE)
                                    @auth
                                        @if(auth()->id() !== $listing->user_id)
                                            <form action="{{ route('user.offer.make', $listing->id) }}" method="POST" class="mb-3">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">@lang('Your Offer')</label>
                                                    <input type="number" name="amount" class="form-control form-control-lg" 
                                                           min="1" step="0.01" required
                                                           placeholder="@lang('Enter your offer')">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">@lang('Message') (@lang('optional'))</label>
                                                    <textarea name="message" class="form-control" rows="2" 
                                                              placeholder="@lang('Add a message to the seller')"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn--base btn-lg w-100">
                                                    <i class="las la-paper-plane"></i> @lang('Make Offer')
                                                </button>
                                            </form>
                                        @else
                                            <div class="alert alert-info">@lang('This is your listing')</div>
                                        @endif
                                    @else
                                        <a href="{{ route('user.login', ['redirect' => route('marketplace.listing.show', $listing->slug)]) }}" class="btn btn--base btn-lg w-100">
                                            @lang('Login to Make Offer')
                                        </a>
                                    @endauth
                                @endif
                            </div>
                        @endif

                        @if(isset($paybackData))
                            <div class="payback-calculator mt-3 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" id="paybackToggle" onclick="document.getElementById('paybackResult').classList.toggle('d-none'); this.classList.toggle('active');">
                                    <i class="las la-calculator"></i> @lang('Determine payback')
                                </button>
                                <div id="paybackResult" class="d-none border rounded p-3 bg-light small">
                                    @if(!$paybackData['can_calculate'])
                                        <p class="text-muted mb-0">{{ $paybackData['message'] }}</p>
                                    @else
                                        <div class="d-flex justify-content-between mb-1"><span>@lang('You pay today'):</span> <strong>{{ showAmount($paybackData['total']) }}</strong></div>
                                        <div class="d-flex justify-content-between mb-1"><span>@lang('Based on listing\'s') {{ $paybackData['label'] }}:</span> <strong>{{ showAmount($paybackData['monthly_amount']) }}/@lang('month')</strong></div>
                                        @php
                                            $months = $paybackData['payback_months'];
                                            $monthsDisplay = $months > 24 ? '24+' : round($months, 1);
                                        @endphp
                                        <div class="d-flex justify-content-between mb-2"><span>@lang('Payback in'):</span> <strong>{{ $monthsDisplay }} @lang('months')</strong></div>
                                        @if($paybackData['payback_ok'])
                                            <p class="text-success mb-1 small">@lang('Based on the listed numbers, payback looks reasonable. You can proceed with confidence.')</p>
                                        @else
                                            <p class="text-muted mb-1 small">@lang('Payback is on the longer side based on current numbers—worth bearing in mind as you decide. If you\'re comfortable with that timeline, you can proceed below.')</p>
                                        @endif
                                        <p class="text-muted mb-0" style="font-size: 0.85em;">@lang('Payback is an estimate based on listed revenue/profit; actual results may vary.')</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Seller Card -->
                <div class="seller-card card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">@lang('About the Seller')</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 60px; height: 60px;">
                                <span class="text-white fs-4">{{ strtoupper(substr($seller->username, 0, 1)) }}</span>
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    <a href="{{ route('marketplace.seller', $seller->username) }}">{{ $seller->fullname }}</a>
                                </h6>
                                <small class="text-muted">{{ '@' . $seller->username }}</small>
                                @if($seller->is_verified_seller)
                                    <span class="badge bg-success">
                                        <i class="las la-check-circle"></i> @lang('Verified')
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="seller-stats">
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <strong>{{ $sellerStats['total_sales'] }}</strong>
                                        <small class="d-block text-muted">@lang('Sales')</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <strong>{{ number_format($sellerStats['avg_rating'], 1) }}</strong>
                                        <small class="d-block text-muted">@lang('Rating')</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <strong>{{ $sellerStats['active_listings'] }}</strong>
                                        <small class="d-block text-muted">@lang('Active')</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="las la-calendar"></i> @lang('Member since') {{ $sellerStats['member_since'] }}
                            </small>
                        </div>
                        
                        <a href="{{ route('marketplace.seller', $seller->username) }}" class="btn btn-outline-secondary w-100 mt-3">
                            @lang('View Profile')
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Similar Listings -->
        @if($similarListings->count() > 0)
        <div class="similar-listings mt-5">
            <h3 class="mb-4">@lang('Similar Listings')</h3>
            <div class="row g-4">
                @foreach($similarListings as $similar)
                    @include($activeTemplate . 'partials.listing_card', ['listing' => $similar])
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('script')
<script>
    function shareUrl() {
        if (navigator.share) {
            navigator.share({
                title: '{{ addslashes($listing->title) }}',
                url: window.location.href
            });
        } else {
            copyListingLink();
        }
    }
    function copyListingLink() {
        var url = window.location.href;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                alert('@lang("Link copied to clipboard!")');
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = url;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            alert('@lang("Link copied to clipboard!")');
        }
    }

    // Fullscreen Gallery Functions (for all listings with images)
    @if($listing->business_type !== 'domain' && $listing->images->count() > 0)
    const allImageUrls = @json($listing->images->map(function($img) {
        return getImage(getFilePath('listing') . '/' . $img->image);
    })->toArray());
    let fullscreenCurrentIndex = 0;

    window.openFullscreenGallery = function(startIndex = 0) {
        const galleryHtml = `
            <div id="fullscreenGallery" class="fullscreen-gallery">
                <div class="gallery-overlay" onclick="closeFullscreenGallery()"></div>
                <div class="gallery-content">
                    <button class="gallery-close" onclick="closeFullscreenGallery()">
                        <i class="las la-times"></i>
                    </button>

                    ${allImageUrls.length > 1 ? `
                    <button class="gallery-nav gallery-prev" onclick="navigateFullscreen(-1)">
                        <i class="las la-chevron-left"></i>
                    </button>
                    ` : ''}

                    <img id="fullscreenImage" src="" alt="" class="gallery-image">

                    ${allImageUrls.length > 1 ? `
                    <button class="gallery-nav gallery-next" onclick="navigateFullscreen(1)">
                        <i class="las la-chevron-right"></i>
                    </button>

                    <div class="gallery-indicators">
                        <span id="fullscreenCounter">1 / ${allImageUrls.length}</span>
                    </div>

                    <div class="gallery-thumbnails">
                        ${allImageUrls.map((imageUrl, index) => `
                            <img src="${imageUrl}"
                                 alt=""
                                 class="gallery-thumb ${index === startIndex ? 'active' : ''}"
                                 onclick="goToFullscreenImage(${index})"
                                 onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">
                        `).join('')}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', galleryHtml);
        document.body.style.overflow = 'hidden';

        // Initialize fullscreen gallery
        fullscreenCurrentIndex = startIndex;
        updateFullscreenImage();

        // Add keyboard support
        document.addEventListener('keydown', handleFullscreenKeydown);
    };

    window.closeFullscreenGallery = function() {
        const gallery = document.getElementById('fullscreenGallery');
        if (gallery) {
            gallery.remove();
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleFullscreenKeydown);
        }
    };

    window.navigateFullscreen = function(direction) {
        const newIndex = fullscreenCurrentIndex + direction;
        if (newIndex >= 0 && newIndex < allImageUrls.length) {
            fullscreenCurrentIndex = newIndex;
            updateFullscreenImage();
        }
    };

    window.goToFullscreenImage = function(index) {
        fullscreenCurrentIndex = index;
        updateFullscreenImage();
    };

    function updateFullscreenImage() {
        const fullscreenImage = document.getElementById('fullscreenImage');
        const fullscreenCounter = document.getElementById('fullscreenCounter');
        const galleryThumbs = document.querySelectorAll('.gallery-thumb');

        if (fullscreenImage && allImageUrls[fullscreenCurrentIndex]) {
            fullscreenImage.src = allImageUrls[fullscreenCurrentIndex];
            fullscreenImage.onerror = function() {
                this.onerror = null;
                this.src = '{{ asset("assets/images/default.png") }}';
            };
        }

        if (fullscreenCounter) {
            fullscreenCounter.textContent = `${fullscreenCurrentIndex + 1} / ${allImageUrls.length}`;
        }

        // Update thumbnail indicators
        galleryThumbs.forEach((thumb, index) => {
            thumb.classList.toggle('active', index === fullscreenCurrentIndex);
        });
    }

    function handleFullscreenKeydown(e) {
        switch(e.key) {
            case 'Escape':
                closeFullscreenGallery();
                break;
            case 'ArrowLeft':
                if (allImageUrls.length > 1) navigateFullscreen(-1);
                break;
            case 'ArrowRight':
                if (allImageUrls.length > 1) navigateFullscreen(1);
                break;
        }
    }

    // Touch gesture support for fullscreen
    let fullscreenTouchStartX = 0;
    let fullscreenTouchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        if (document.getElementById('fullscreenGallery')) {
            fullscreenTouchStartX = e.changedTouches[0].screenX;
        }
    });

    document.addEventListener('touchend', function(e) {
        if (document.getElementById('fullscreenGallery')) {
            fullscreenTouchEndX = e.changedTouches[0].screenX;
            handleFullscreenSwipe();
        }
    });

    function handleFullscreenSwipe() {
        if (allImageUrls.length <= 1) return;
        const swipeThreshold = 50;
        if (fullscreenTouchEndX < fullscreenTouchStartX - swipeThreshold) {
            navigateFullscreen(1); // Swipe left - next
        } else if (fullscreenTouchEndX > fullscreenTouchStartX + swipeThreshold) {
            navigateFullscreen(-1); // Swipe right - previous
        }
    }
    @endif

    // Enhanced Image Gallery with Animations (only for non-domain listings with multiple images)
    @if($listing->business_type !== 'domain' && $listing->images->count() > 1)
    document.addEventListener('DOMContentLoaded', function() {
        const imageUrls = @json($listing->images->map(function($img) {
            return getImage(getFilePath('listing') . '/' . $img->image);
        })->toArray());
        let currentIndex = 0;
        const mainImage = document.getElementById('mainImage');
        const thumbnailItems = document.querySelectorAll('.thumbnail-item');
        const prevArrow = document.getElementById('prevImage');
        const nextArrow = document.getElementById('nextImage');
        const currentImageIndex = document.getElementById('currentImageIndex');
        const thumbnailStrip = document.getElementById('thumbnailStrip');

        // Image navigation functions
        function updateMainImage(index, animate = true) {
            if (!mainImage || index < 0 || index >= imageUrls.length) return;

            if (animate) {
                // Fade out current image
                mainImage.style.opacity = '0.5';

                setTimeout(() => {
                    mainImage.src = imageUrls[index];
                    mainImage.onload = function() {
                        mainImage.style.opacity = '1';
                    };
                    mainImage.onerror = function() {
                        this.onerror = null;
                        this.src = '{{ asset("assets/images/default.png") }}';
                        mainImage.style.opacity = '1';
                    };
                }, 150);
            } else {
                mainImage.src = imageUrls[index];
                mainImage.onerror = function() {
                    this.onerror = null;
                    this.src = '{{ asset("assets/images/default.png") }}';
                };
            }

            // Update thumbnails
            thumbnailItems.forEach((thumb, i) => {
                if (i === index) {
                    thumb.classList.add('active');
                    thumb.style.borderColor = '#007bff';
                    thumb.style.transform = 'scale(1.05)';
                } else {
                    thumb.classList.remove('active');
                    thumb.style.borderColor = 'transparent';
                    thumb.style.transform = 'scale(1)';
                }
            });

            // Update counter
            if (currentImageIndex) {
                currentImageIndex.textContent = index + 1;
            }

            // Update navigation arrows
            if (prevArrow) {
                prevArrow.style.display = index === 0 ? 'none' : 'block';
            }
            if (nextArrow) {
                nextArrow.style.display = index === imageUrls.length - 1 ? 'none' : 'block';
            }

            // Auto-scroll thumbnail into view (only within thumbnail container, not the page)
            const activeThumbnail = document.querySelector('.thumbnail-item.active');
            if (activeThumbnail && thumbnailStrip) {
                const thumbnailRect = activeThumbnail.getBoundingClientRect();
                const containerRect = thumbnailStrip.getBoundingClientRect();
                const thumbnailLeft = activeThumbnail.offsetLeft;
                const thumbnailWidth = activeThumbnail.offsetWidth;
                const containerWidth = thumbnailStrip.offsetWidth;
                const scrollLeft = thumbnailStrip.scrollLeft;
                
                // Calculate the position to center the thumbnail in the container
                const targetScroll = thumbnailLeft - (containerWidth / 2) + (thumbnailWidth / 2);
                
                // Smooth scroll only the thumbnail container, not the page
                thumbnailStrip.scrollTo({
                    left: targetScroll,
                    behavior: 'smooth'
                });
            }
        }

        // Thumbnail click handlers
        thumbnailItems.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', function() {
                currentIndex = index;
                updateMainImage(currentIndex);
            });

            // Hover effects
            thumbnail.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1.1)';
                    this.style.borderColor = '#6c757d';
                }
            });

            thumbnail.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1)';
                    this.style.borderColor = 'transparent';
                }
            });
        });

        // Navigation arrows
        if (prevArrow) {
            prevArrow.addEventListener('click', function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateMainImage(currentIndex);
                }
            });
        }

        if (nextArrow) {
            nextArrow.addEventListener('click', function() {
                if (currentIndex < images.length - 1) {
                    currentIndex++;
                    updateMainImage(currentIndex);
                }
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' && currentIndex > 0) {
                currentIndex--;
                updateMainImage(currentIndex);
            } else if (e.key === 'ArrowRight' && currentIndex < imageUrls.length - 1) {
                currentIndex++;
                updateMainImage(currentIndex);
            }
        });

        // Touch/swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        mainImage.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        mainImage.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchEndX < touchStartX - swipeThreshold && currentIndex < imageUrls.length - 1) {
                // Swipe left - next image
                currentIndex++;
                updateMainImage(currentIndex);
            } else if (touchEndX > touchStartX + swipeThreshold && currentIndex > 0) {
                // Swipe right - previous image
                currentIndex--;
                updateMainImage(currentIndex);
            }
        }

        // Auto-play functionality (optional)
        let autoPlayInterval = null;
        const autoPlayDelay = 4000; // 4 seconds

        function startAutoPlay() {
            if (imageUrls.length > 1) {
                autoPlayInterval = setInterval(() => {
                    currentIndex = (currentIndex + 1) % imageUrls.length;
                    updateMainImage(currentIndex);
                }, autoPlayDelay);
            }
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }

        // Pause auto-play on user interaction
        const galleryContainer = document.querySelector('.listing-images');
        if (galleryContainer) {
            ['click', 'touchstart', 'keydown'].forEach(event => {
                galleryContainer.addEventListener(event, stopAutoPlay, { passive: true });
            });
        }

        // Start auto-play after 5 seconds of inactivity
        let autoPlayTimeout = setTimeout(startAutoPlay, 5000);

        function resetAutoPlayTimer() {
            clearTimeout(autoPlayTimeout);
            stopAutoPlay();
            autoPlayTimeout = setTimeout(startAutoPlay, 5000);
        }

        // Reset auto-play timer on any user interaction
        if (galleryContainer) {
            galleryContainer.addEventListener('mouseenter', resetAutoPlayTimer);
            galleryContainer.addEventListener('touchstart', resetAutoPlayTimer, { passive: true });
        }

        // Preload images for better performance
        function preloadImages() {
            imageUrls.forEach((imageUrl, index) => {
                if (index !== 0) { // Skip first image as it's already loaded
                    const img = new Image();
                    img.src = imageUrl;
                }
            });
        }

        preloadImages();

        // Initialize gallery state
        updateMainImage(0, false);

        // Initialize gallery state
        updateMainImage(0, false);
    });
    @endif
</script>





@endpush

