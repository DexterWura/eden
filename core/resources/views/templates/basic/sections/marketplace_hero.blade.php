@php
    $content = getContent('marketplace_hero.content', true);
    if (!$content) return;

    $heroHeading = trim((string) (@$content->data_values->heading ?? ''));
    $titleLine1 = $heroHeading !== '' ? $heroHeading : __('#1 Platform to Buy & Sell');

    $fallbackRaw = trim((string) (@$content->data_values->trending_fallback ?? ''));
    $fallbackWords = $fallbackRaw !== ''
        ? array_slice(array_filter(array_map('trim', explode(',', $fallbackRaw))), 0, 6)
        : null;
    $trendingTags = getTrendingListingTags($fallbackWords);

    // Typing words: "Online Businesses" + only business types enabled in marketplace settings
    $typingLabels = [
        'domain' => __('Domain Names'),
        'website' => __('SaaS Websites'),
        'social_media_account' => __('Social Accounts'),
        'mobile_app' => __('Mobile Apps'),
        'desktop_app' => __('Desktop Apps'),
    ];
    $typingTexts = [__('Online Businesses')];
    foreach ($typingLabels as $type => $label) {
        if (\App\Models\MarketplaceSetting::allowBusinessType($type)) {
            $typingTexts[] = $label;
        }
    }
@endphp

<section class="marketplace-hero" style="position: relative;">
    <div class="hero-background">
        <div class="wave-pattern"></div>
    </div>
    
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                
                {{-- Main Heading --}}
                <h1 class="hero-title animate-fade-up">
                    <span class="title-line-1">{{ $titleLine1 }}</span>
                    <span class="highlight title-line-2">
                        <span class="typing-text" data-texts="{{ json_encode($typingTexts) }}">{{ $typingTexts[0] }}</span>
                        <span class="typing-cursor">|</span>
                    </span>
                </h1>
                {{-- Search Bar --}}
                <div class="hero-search-wrapper animate-fade-up-delay-1">
                    <form action="{{ route('marketplace.browse') }}" method="GET" class="hero-search-form">
                        <div class="search-input-wrapper">
                            <input type="text" name="search" class="hero-search-input" 
                                   placeholder="@lang('e.g. Shopify Stores, SaaS, Blogs...')">
                            <button type="submit" class="hero-search-btn">
                                <i class="las la-search"></i>
                                <span>@lang('Search')</span>
                            </button>
                        </div>
                    </form>
                </div>
                
                {{-- Trending Tags --}}
                <div class="trending-tags animate-fade-up-delay-2">
                    <span class="trending-label">@lang('Trending'):</span>
                    <div class="tags-wrapper">
                        @foreach($trendingTags as $tag)
                            <a href="{{ route('marketplace.browse', ['search' => $tag['search']]) }}" class="trend-tag tag-{{ $loop->iteration }}">{{ $tag['label'] }}</a>
                        @endforeach
                    </div>
                </div>
                
                {{-- CTA Button --}}
                <div class="hero-cta animate-fade-up-delay-3">
                    @auth
                        <a href="{{ route('user.listing.create') }}" class="cta-btn">
                            @lang('Start Selling Now')
                        </a>
                    @else
                        <a href="{{ route('user.register') }}" class="cta-btn">
                            @lang('Sign up for free. No credit card required')
                        </a>
                    @endauth
                </div>
                
                {{-- Stats removed - not exposing business metrics publicly --}}
                
                {{-- Business Type Icons --}}
                <div class="business-types animate-fade-up-delay-5">
                    <a href="{{ route('marketplace.type', 'domain') }}" class="type-icon icon-1" title="@lang('Domains')">
                        <i class="las la-globe"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'website') }}" class="type-icon icon-2" title="@lang('Websites')">
                        <i class="las la-laptop"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'mobile_app') }}" class="type-icon icon-3" title="@lang('Mobile Apps')">
                        <i class="las la-mobile-alt"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'desktop_app') }}" class="type-icon icon-4" title="@lang('Desktop Apps')">
                        <i class="las la-desktop"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'social_media_account') }}" class="type-icon icon-5" title="@lang('Social Media')">
                        <i class="las la-share-alt"></i>
                    </a>
                    <a href="{{ route('marketplace.auctions') }}" class="type-icon type-icon--highlight icon-6" title="@lang('Live Auctions')">
                        <i class="las la-gavel"></i>
                    </a>
                </div>
                
            </div>
        </div>
    </div>

    <a href="#" class="hero-scroll-indicator" aria-label="@lang('Scroll down to see listings')" data-scroll-to-next>
        <span class="hero-scroll-indicator__arrows">
            <i class="las la-chevron-down"></i>
            <i class="las la-chevron-down"></i>
        </span>
        <span class="hero-scroll-indicator__label">@lang('Browse listings')</span>
    </a>
</section>

@push('style')
<style>
    .hero-title .highlight {
        color: #{{ gs('base_color') }} !important;
    }
    
    .hero-search-btn {
        background: #{{ gs('base_color') }} !important;
    }
    
    .hero-search-btn:hover {
        background: #{{ gs('base_color') }} !important;
        filter: brightness(0.85);
        box-shadow: 0 10px 30px #{{ gs('base_color') }}66;
    }
    
    /* Wave Animation */
    .wave-pattern {
        animation: wave-animation 20s ease-in-out infinite;
    }
    
    @keyframes wave-animation {
        0%, 100% {
            transform: translateX(0) translateY(0);
        }
        50% {
            transform: translateX(-50px) translateY(-20px);
        }
    }
    
    /* Fade Up Animations */
    .animate-fade-up {
        animation: fadeUp 0.8s ease-out forwards;
        opacity: 0;
    }
    
    .animate-fade-up-delay-1 {
        animation: fadeUp 0.8s ease-out 0.2s forwards;
        opacity: 0;
    }
    
    .animate-fade-up-delay-2 {
        animation: fadeUp 0.8s ease-out 0.4s forwards;
        opacity: 0;
    }
    
    .animate-fade-up-delay-3 {
        animation: fadeUp 0.8s ease-out 0.6s forwards;
        opacity: 0;
    }
    
    .animate-fade-up-delay-4 {
        animation: fadeUp 0.8s ease-out 0.8s forwards;
        opacity: 0;
    }
    
    .animate-fade-up-delay-5 {
        animation: fadeUp 0.8s ease-out 1s forwards;
        opacity: 0;
    }
    
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Title Line Animations */
    .title-line-1 {
        display: block;
        animation: slideInLeft 0.8s ease-out forwards;
        opacity: 0;
    }
    
    .title-line-2 {
        display: block;
        animation: slideInRight 0.8s ease-out 0.3s forwards;
        opacity: 0;
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Search Bar Animation */
    .search-input-wrapper {
        animation: scaleIn 0.6s ease-out 0.4s forwards;
        transform: scale(0.9);
        opacity: 0;
    }
    
    @keyframes scaleIn {
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .hero-search-input:focus {
        animation: pulse 2s ease-in-out infinite;
        outline: none;
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(37, 99, 235, 0);
        }
    }
    
    /* Trending Tags Staggered Animation */
    .trend-tag {
        opacity: 0;
        transform: translateY(20px) scale(0.8);
        animation: tagPopIn 0.5s ease-out forwards;
    }
    
    .tag-1 { animation-delay: 0.6s; }
    .tag-2 { animation-delay: 0.7s; }
    .tag-3 { animation-delay: 0.8s; }
    .tag-4 { animation-delay: 0.9s; }
    .tag-5 { animation-delay: 1.0s; }
    .tag-6 { animation-delay: 1.1s; }
    
    @keyframes tagPopIn {
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .trend-tag:hover {
        transform: translateY(-5px) scale(1.05);
        transition: all 0.3s ease;
    }
    
    /* CTA Button Animation */
    .cta-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .cta-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .cta-btn:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    
    /* Business Type Icons Staggered Animation */
    .type-icon {
        opacity: 0;
        transform: translateY(30px) rotate(-180deg);
        animation: iconSpinIn 0.6s ease-out forwards;
    }
    
    .icon-1 { animation-delay: 1.2s; }
    .icon-2 { animation-delay: 1.3s; }
    .icon-3 { animation-delay: 1.4s; }
    .icon-4 { animation-delay: 1.5s; }
    .icon-5 { animation-delay: 1.6s; }
    .icon-6 { animation-delay: 1.7s; }
    
    @keyframes iconSpinIn {
        to {
            opacity: 1;
            transform: translateY(0) rotate(0deg);
        }
    }
    
    .type-icon:hover {
        transform: translateY(-10px) scale(1.2) rotate(5deg);
        transition: all 0.3s ease;
    }
    
    .type-icon--highlight {
        animation: iconPulse 2s ease-in-out infinite;
        animation-delay: 1.7s;
    }
    
    @keyframes iconPulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
        }
        50% {
            transform: scale(1.1);
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }
    }
    
    /* Stats Text Animation */
    .stats-text strong {
        display: inline-block;
        animation: numberCountUp 1s ease-out 1.2s forwards;
        opacity: 0;
        transform: translateY(10px);
    }
    
    @keyframes numberCountUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Search Button Icon Animation */
    .hero-search-btn i {
        transition: transform 0.3s ease;
    }
    
    .hero-search-btn:hover i {
        transform: rotate(15deg) scale(1.1);
    }
    
    /* Typing Effect Styles */
    .typing-text {
        display: inline-block;
    }
    
    .typing-cursor {
        display: inline-block;
        margin-left: 2px;
        animation: blink 1s infinite;
        color: #{{ gs('base_color') }};
    }
    
    @keyframes blink {
        0%, 50% {
            opacity: 1;
        }
        51%, 100% {
            opacity: 0;
        }
    }

    /* Scroll indicator – bottom center */
    .hero-scroll-indicator {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .hero-scroll-indicator:hover {
        color: #fff;
    }
    .hero-scroll-indicator__arrows {
        display: flex;
        flex-direction: column;
        align-items: center;
        animation: bounceDown 1.8s ease-in-out infinite;
    }
    .hero-scroll-indicator__arrows i {
        font-size: 1.25rem;
        line-height: 0.6;
    }
    .hero-scroll-indicator__label {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        opacity: 0.85;
    }
    @keyframes bounceDown {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(8px);
        }
    }
</style>
@endpush

@push('script')
<script>
    (function() {
        const typingElement = document.querySelector('.typing-text');
        if (!typingElement) return;
        
        const texts = JSON.parse(typingElement.getAttribute('data-texts'));
        const cursor = document.querySelector('.typing-cursor');
        let currentTextIndex = 0;
        let currentCharIndex = 0;
        let isDeleting = false;
        let typingSpeed = 100; // milliseconds per character
        let deletingSpeed = 50; // faster when deleting
        let pauseTime = 2000; // pause at end of each text
        
        function typeText() {
            const currentText = texts[currentTextIndex];
            
            if (isDeleting) {
                // Delete characters
                typingElement.textContent = currentText.substring(0, currentCharIndex - 1);
                currentCharIndex--;
                typingSpeed = deletingSpeed;
                
                if (currentCharIndex === 0) {
                    isDeleting = false;
                    currentTextIndex = (currentTextIndex + 1) % texts.length;
                    typingSpeed = 100; // Reset to normal typing speed
                }
            } else {
                // Type characters
                typingElement.textContent = currentText.substring(0, currentCharIndex + 1);
                currentCharIndex++;
                typingSpeed = 100;
                
                if (currentCharIndex === currentText.length) {
                    // Finished typing, pause then start deleting
                    isDeleting = true;
                    typingSpeed = pauseTime;
                }
            }
            
            setTimeout(typeText, typingSpeed);
        }
        
        // Start typing effect after a short delay
        setTimeout(() => {
            typeText();
        }, 1500); // Wait for initial animation to complete
    })();

    (function() {
        const trigger = document.querySelector('[data-scroll-to-next]');
        if (!trigger) return;
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const hero = document.querySelector('.marketplace-hero');
            const next = hero && hero.nextElementSibling;
            if (next) {
                next.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    })();
</script>
@endpush
