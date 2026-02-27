<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ gs()->siteName(__($pageTitle)) }}</title>
    @include('partials.seo')

    {{-- Organization and WebSite structured data for rich search results --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "name": "{{ gs('site_name') }}",
                "url": "{{ url('/') }}",
                "logo": "{{ siteLogo() }}"
            },
            {
                "@type": "WebSite",
                "url": "{{ url('/') }}",
                "name": "{{ gs('site_name') }}",
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "{{ route('marketplace.browse') }}?search={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        ]
    }
    </script>
    @stack('structured-data')

    {{-- PWA manifest --}}
    <link rel="manifest" href="{{ route('manifest') }}">

    {{-- Critical CSS for instant preloader display --}}
    <style>.preloader{position:fixed;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg,#0b1437 0%,#1e3a5f 100%);display:flex;align-items:center;justify-content:center;z-index:99999;transition:opacity .15s ease-out,visibility .15s}.preloader.hidden{opacity:0;visibility:hidden;pointer-events:none}.preloader__loader{width:40px;height:40px;position:relative}.preloader__loader::before{content:'';position:absolute;top:0;left:0;width:100%;height:100%;border:3px solid rgba(255,255,255,.1);border-radius:50%}.preloader__loader::after{content:'';position:absolute;top:0;left:0;width:100%;height:100%;border:3px solid transparent;border-top-color:hsl(var(--base,217 91% 60%));border-radius:50%;animation:spin .3s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}</style>

    {{-- Resource hints for faster loading --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="preload" href="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}" as="script">

    {{-- Critical CSS loaded synchronously --}}
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Icon libraries loaded asynchronously (non-blocking) --}}
    <link rel="preload" href="{{ asset('assets/global/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}"></noscript>
    <link rel="preload" href="{{ asset('assets/global/css/line-awesome.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}"></noscript>

    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/slick.css') }}" />
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/main.css') }}" />
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/marketplace-gallery.css') }}" />
    @stack('style-lib')
    @stack('style')
    <link rel="stylesheet"
        href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ gs('base_color') }}&secondColor={{ gs('secondary_color') }}">

    {{-- Datafast Analytics --}}
    <script
        defer
        data-website-id="dfid_5qFrGaGlZF9mnWwxVLRcU"
        data-domain="flipit.co.zw"
        src="https://datafa.st/js/script.js">
    </script>
    @if(gs('google_adsense_enable') && gs('google_adsense_script'))
        {{-- SAFE: Admin-controlled setting containing Google Adsense script --}}
        {!! gs('google_adsense_script') !!}
    @endif
</head>

<body>
    @stack('fbComment')


    <div class="preloader" id="preloader">
        <div class="preloader__loader"></div>
    </div>
    <script>document.addEventListener('DOMContentLoaded',function(){document.getElementById('preloader').classList.add('hidden');});</script>

    <div class="back-to-top">
        <span class="back-top">
            <i class="las la-angle-double-up"></i>
        </span>
    </div>

    @yield('panel')


    @if (gs()->gdpr_cookie == Status::ENABLE && !\Cookie::get('gdpr_cookie'))
        @php
            $cookie = App\Models\Frontend::where('data_keys', 'cookie.data')->first();
        @endphp
        <div class="cookies-card text-center hide">
            <div class="cookies-card__icon bg--base">
                <i class="las la-cookie-bite "></i>
            </div>
            <p class="mt-4 cookies-card__content">{{ @$cookie->data_values->short_desc }} <a href="{{ route('cookie.policy') }}"
                    target="_blank">@lang('learn more')</a>
            </p>
            <div class="cookies-card__btn mt-4">
                <a href="javascript:void(0)" class="btn btn--base w-100 policy">@lang('Allow')</a>
            </div>
        </div>
    @endif

    <div class="overlay"></div>

    {{-- jQuery loaded sync (other scripts depend on it) --}}
    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    {{-- Non-critical scripts deferred for faster initial load --}}
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset($activeTemplateTrue . 'js/slick.js') }}" defer></script>
    <script src="{{ asset($activeTemplateTrue . 'js/jquery.nice-select.js') }}" defer></script>
    <script src="{{ asset($activeTemplateTrue . 'js/app.js') }}" defer></script>

    @stack('script-lib')

    {{-- Third-party scripts loaded asynchronously to avoid blocking --}}
    @php
        $gaScript = loadExtension('google-analytics');
        $tawkScript = loadExtension('tawk-chat');
    @endphp
    @if($gaScript)
        {{-- SAFE: Admin-controlled extension for Google Analytics tracking --}}
        {!! preg_replace('/<script(?=\s|>)/', '<script async', $gaScript) !!}
    @endif
    @if($tawkScript)
        {{-- SAFE: Admin-controlled extension for Tawk chat widget --}}
        {!! preg_replace('/<script(?=\s|>)/', '<script defer', $tawkScript) !!}
    @endif

    @include('partials.notify')

    @if (gs('pn'))
        @include('partials.push_script')
    @endif
    @stack('script')

    <script>
        (function($) {
            "use strict";
            $(".langSel").on("change", function() {
                window.location.href = "{{ route('home') }}/change/" + $(this).val();
            });

            $('.select2').each(function(index, element) {
                $(element).select2({
                    minimumResultsForSearch: "-1"
                });
            });

            $('.select2-basic').each(function(index, element) {
                $(element).select2({
                    dropdownParent: $(element).closest('.select2-parent')
                });
            });


            $('.policy').on('click', function() {
                $.get('{{ route('cookie.accept') }}',
                    function(response) {
                        $('.cookies-card').addClass('d-none');
                    });
            });

            setTimeout(function() {
                $('.cookies-card').removeClass('hide')
            }, 2000);



            var inputElements = $('[type=text],[type=password],select,textarea');
            $.each(inputElements, function(index, element) {
                element = $(element);
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'))
            });

            $.each($('input, select, textarea'), function(i, element) {
                var elementType = $(element);
                if (elementType.attr('type') != 'checkbox') {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').addClass('required');
                    }
                }

            });

            $.each($('input:not([type=checkbox]):not([type=hidden]), select, textarea'), function(i, element) {

                if (element.hasAttribute('required')) {
                    $(element).closest('.form-group').find('label').addClass('required');
                }

            });


            $('.showFilterBtn').on('click', function() {
                $('.responsive-filter-card').slideToggle();
            });



            Array.from(document.querySelectorAll('table')).forEach(table => {
                let heading = table.querySelectorAll('thead tr th');
                Array.from(table.querySelectorAll('tbody tr')).forEach((row) => {
                    Array.from(row.querySelectorAll('td')).forEach((colum, i) => {
                        colum.setAttribute('data-label', heading[i].innerText)
                    });
                });
            });

            let disableSubmission = false;
            $('.disableSubmission').on('submit', function(e) {
                if (disableSubmission) {
                    e.preventDefault()
                } else {
                    disableSubmission = true;
                }
            });

            // Lazy loading for images with early trigger
            function lazyLoadImages() {
                const lazyImages = document.querySelectorAll('img.lazy');

                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                }, {
                    rootMargin: '100px 0px' // Start loading 100px before entering viewport
                });

                lazyImages.forEach(img => {
                    imageObserver.observe(img);
                });
            }

            // Initialize lazy loading when DOM is ready
            if ('IntersectionObserver' in window) {
                lazyLoadImages();
            } else {
                // Fallback for browsers without IntersectionObserver
                const lazyImages = document.querySelectorAll('img.lazy');
                lazyImages.forEach(img => {
                    img.src = img.dataset.src;
                });
            }
        })(jQuery);
    </script>
</body>

</html>
