@php
    $socialContent = getContent('social_icon.content', true);
    $socialElements = getContent('social_icon.element', orderById: true);
    $contactElements = getContent('contact.element', orderById: true);
    $policyElements = getContent('policy_pages.element', orderById: true);
    $footerContent = getContent('footer_section.content', true);
@endphp

<div style="display:flex; justify-content:center; padding:16px 0;">
    <div class="MainAdverTiseMentDiv" data-publisher="eyJpdiI6InpsbjBkRVNsSTg0YVpndEFVdCt1Mmc9PSIsInZhbHVlIjoiUnJTUHc3TzRpT3UzVWxZR3ozL0xidz09IiwibWFjIjoiMTk2MTE2YTk1YmUxZmRlZGFlMzRhNmQ2ZGRmY2E5MDBhZWQwYjk4Mjc2MDhiNmZjNmJlYTM2MjAyZDdiMDRjYiIsInRhZyI6IiJ9" data-adsize="970x90"></div>
    <script class="adScriptClass" src="https://zimadsense.com/assets/ads/ad.js"></script>
</div>

<footer class="footer">

    <div class="section bg--accent">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-6 col-lg-6  col-xxl-4">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <a href="https://www.producthunt.com/products/flipit-3?embed=true&amp;utm_source=badge-featured&amp;utm_medium=badge&amp;utm_campaign=badge-flipit-3" target="_blank" rel="noopener noreferrer">
                            <img alt="FLIPit - #1 Marketplace for Buying and Selling Online Businesses | Product Hunt" width="250" height="54" src="https://api.producthunt.com/widgets/embed-image/v1/featured.svg?post_id=1075151&amp;theme=light&amp;t=1770620946564">
                        </a>
                        <a href="https://startupfa.me/s/flipit?utm_source=flipit.co.zw" target="_blank" rel="noopener noreferrer">
                            <img src="https://startupfa.me/badges/featured-badge.webp" alt="FLIPit - Featured on Startup Fame" width="171" height="54" />
                        </a>
                        <a href="https://tinylaunch.com" target="_blank" rel="noopener">
                            <img src="https://tinylaunch.com/tinylaunch_badge_launching_soon.svg" alt="TinyLaunch Badge" style="width:202px; height:auto;" />
                        </a>
                    </div>
                    <a href="{{ route('home') }}" class="logo mt-0">
                        <img src="{{ siteLogo() }}" alt="image" class="img-fluid logo__is">
                    </a>
                    <hr class="footer-hr">
                    <p class="text--white mb-4">{{ __(@$footerContent->data_values->footer_text) }}</p>
                    <ul class="list list--row">
                        @foreach ($socialElements as $social)
                            <li class="list--row__item">
                                <a href="{{ @$social->data_values->url }}" class="social-icon t-link icon icon--sm icon--circle" target="_blank">
                                    @php echo @$social->data_values->icon @endphp
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-md-6 col-lg-6 col-xxl-2">
                    <h5 class="mt-0 text--white">@lang('Quick Links')</h5>
                    <hr class="footer-hr">
                    <ul class="list list--column">
                        <li class="list--column__item">
                            <a href="{{ route('home') }}" class="t-link t-link--base text--white d-inline-block">
                                @lang('Home')
                            </a>
                        </li>
                        @foreach ($pages as $data)
                            <li class="list--column__item">
                                <a href="{{ route('pages', [$data->slug]) }}" class="t-link t-link--base text--white d-inline-block">
                                    {{ __($data->name) }}
                                </a>
                            </li>
                        @endforeach
                        <li class="list--column__item">
                            <a href="{{ route('blogs') }}" class="t-link t-link--base text--white d-inline-block">
                                @lang('Blogs')
                            </a>
                        </li>
                        <li class="list--column__item">
                            <a href="{{ route('contact') }}" class="t-link t-link--base text--white d-inline-block">
                                @lang('Contact')
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="col-md-6 col-lg-6 col-xxl-3">
                    <h5 class="mt-0 text--white">@lang('Company Policy')</h5>
                    <hr class="footer-hr">
                    <ul class="list list--column">
                        @foreach ($policyElements as $policy)
                            <li class="list--column__item">
                                <a href="{{ route('policy.pages', $policy->slug) }}" class="t-link t-link--base text--white d-inline-block">
                                    {{ strLimit(__(@$policy->data_values->title), 25) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-6 col-lg-6  col-xxl-3">
                    <h5 class="mt-0 text--white">@lang('Contact Us')</h5>
                    <hr class="footer-hr">
                    <ul class="list list--column">
                        @foreach ($contactElements as $contact)
                            <li class="list--column__item">
                                <div class="contact-card">
                                    <div class="contact-card__icon">
                                        @php echo @$contact->data_values->icon @endphp
                                    </div>
                                    <div class="contact-card__content">
                                        <p class="text--white mb-0">
                                            {{ __(@$contact->data_values->details) }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer__copyright py-3">
        <p class="mb-0 sm-text text--white text-center">
            @lang('Copyright') &copy; {{ date('Y') }}. @lang('All Rights Reserved')
        </p>
    </div>

</footer>
