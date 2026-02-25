<header class="header-fixed header--primary">
    <div class="container">
        <div class="row g-0 align-items-center">
            <div class="col-6 col-lg-2">
                <a class="logo" href="{{ route('home') }}">
                    <img alt="@lang('image')" class="img-fluid logo__is" src="{{ siteLogo() }}">
                </a>
            </div>
            <div class="col-6 col-lg-10">
                <div class="nav-container">
                    <!-- Navigation Toggler  -->
                    <div class="d-flex justify-content-end align-items-center d-xl-none">
                        <button class="btn p-0 nav--toggle header-button text-white" type="button">
                            <i class="las la-bars"></i>
                        </button>
                    </div>
                    <!-- Navigation Toggler End -->

                    <!-- Navigation  -->
                    <nav class="navs">
                        <!-- Primary Menu  -->
                        <div class="header-menu">
                            <ul class="list primary-menu primary-menu--alt">
                                <li class="primary-menu__list">
                                    <a class="primary-menu__link" href="{{ route('home') }}">@lang('Home')</a>
                                </li>

                                @if ((auth()->user() && request()->routeIs('user.*')) || (auth()->user() && request()->routeIs('ticket*')) || (auth()->user() && request()->routeIs('marketplace*')))

                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Marketplace')</a>

                                        <ul class="primary-menu__sub">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.index') }}">@lang('Browse All')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.auctions') }}">@lang('Live Auctions')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.listing.create') }}">@lang('Sell Your Business')</a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">@lang('My Account')</a>

                                        <ul class="primary-menu__sub">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.listing.index') }}">@lang('My Listings')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.bid.index') }}">@lang('My Bids')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.offer.index') }}">@lang('My Offers')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.watchlist.index') }}">@lang('Watchlist')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.escrow.index') }}">@lang('My Purchases')</a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Deposit')</a>

                                        <ul class="primary-menu__sub">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.deposit.index') }}">@lang('Deposit Now')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.deposit.history') }}">@lang('Deposit Log')</a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Withdraw')</a>

                                        <ul class="primary-menu__sub">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link" href="{{ route('user.withdraw') }}">@lang('Withdraw Now')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.withdraw.history') }}">@lang('Withdrawal Log')</a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="primary-menu__list">
                                        <a class="primary-menu__link" href="{{ route('user.transactions') }}">@lang('Transactions')</a>
                                    </li>

                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Tools')</a>

                                        <ul class="primary-menu__sub primary-menu__sub--tools">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.valuation') }}">
                                                    <i class="las la-calculator me-1"></i> @lang('Business Valuation')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.seller-earnings') }}">
                                                    <i class="las la-coins me-1"></i> @lang('Seller Earnings')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.compare') }}">
                                                    <i class="las la-balance-scale me-1"></i> @lang('Listing Comparison')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.payback') }}">
                                                    <i class="las la-clock me-1"></i> @lang('Buyer Payback Calculator')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.index') }}">
                                                    <i class="las la-th-list me-1"></i> @lang('All Tools')
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">{{ auth()->user()->username }}</a>

                                        <ul class="primary-menu__sub">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link" href="{{ route('ticket.index') }}">@lang('Support Tickets')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link" href="{{ route('ticket.open') }}">@lang('Open New Ticket')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.change.password') }}">@lang('Change Password')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('user.profile.setting') }}">@lang('Profile Setting')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link" href="{{ route('user.twofactor') }}">@lang('2FA Security')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link" href="{{ route('user.logout') }}">@lang('Logout')</a>
                                            </li>
                                        </ul>
                                    </li>
                                @else
                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Marketplace')</a>

                                        <ul class="primary-menu__sub">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.index') }}">@lang('Browse All')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.auctions') }}">@lang('Live Auctions')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.type', 'domain') }}">@lang('Domains')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.type', 'website') }}">@lang('Websites')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.type', 'social_media_account') }}">@lang('Social Media')</a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('marketplace.type', 'mobile_app') }}">@lang('Mobile Apps')</a>
                                            </li>
                                        </ul>
                                    </li>

                                    @foreach ($pages as $k => $data)
                                        <li class="primary-menu__list">
                                            <a class="primary-menu__link" href="{{ route('pages', [$data->slug]) }}">{{ __($data->name) }}</a>
                                        </li>
                                    @endforeach

                                    <li class="primary-menu__list has-sub">
                                        <a class="primary-menu__link" href="javascript:void(0)">@lang('Tools')</a>

                                        <ul class="primary-menu__sub primary-menu__sub--tools">
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.valuation') }}">
                                                    <i class="las la-calculator me-1"></i> @lang('Business Valuation')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.seller-earnings') }}">
                                                    <i class="las la-coins me-1"></i> @lang('Seller Earnings')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.compare') }}">
                                                    <i class="las la-balance-scale me-1"></i> @lang('Listing Comparison')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.payback') }}">
                                                    <i class="las la-clock me-1"></i> @lang('Buyer Payback Calculator')
                                                </a>
                                            </li>
                                            <li class="primary-menu__sub-list">
                                                <a class="t-link primary-menu__sub-link"
                                                    href="{{ route('tools.index') }}">
                                                    <i class="las la-th-list me-1"></i> @lang('All Tools')
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="primary-menu__list">
                                        <a class="primary-menu__link" href="{{ route('blogs') }}">@lang('Blogs')</a>
                                    </li>

                                    <li class="primary-menu__list">
                                        <a class="primary-menu__link" href="{{ route('contact') }}">@lang('Contact')</a>
                                    </li>
                                @endif
                                @if (gs('multi_language'))
                                    <li class="language_switcher me-3">
                                        @php
                                            $language = App\Models\Language::all();
                                            $selectLang = $language->where('code', config('app.locale'))->first();

                                        @endphp
                                        <div class="language_switcher__caption">
                                            <span class="icon">
                                                <img src="{{ getImage(getFilePath('language') . '/' . $selectLang->image, getFileSize('language')) }}"
                                                    alt="@lang('image')">
                                            </span>
                                            <span class="text"> {{ __(@$selectLang->name) }} </span>
                                        </div>
                                        <div class="language_switcher__list">
                                            @foreach ($language as $item)
                                                <div class="language_switcher__item    @if (session('lang') == $item->code) selected @endif"
                                                    data-value="{{ $item->code }}">
                                                    <a href="{{ route('lang', $item->code) }}" class="thumb">
                                                        <span class="icon">
                                                            <img src="{{ getImage(getFilePath('language') . '/' . $item->image, getFileSize('language')) }}"
                                                                alt="@lang('image')">
                                                        </span>
                                                        <span class="text"> {{ __($item->name) }}</span>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
                                @guest
                                    <li class="primary-menu__list">
                                        <a class="btn btn-base--outline " href="{{ route('user.login') }}">@lang('Login')</a>
                                        <a class="btn btn--md btn--base  ms-3" href="{{ route('user.register') }}">@lang('Join Now')</a>
                                    </li>
                                @else
                                    <li class="primary-menu__list">
                                        <a class="btn btn--md btn-base--outline " href="{{ route('user.home') }}">@lang('Dashboard')</a>
                                    </li>
                                @endguest
                            </ul>
                        </div>
                        <!-- User Login End -->
                    </nav>
                    <!-- Navigation End -->
                </div>
            </div>
        </div>
    </div>
</header>

@push('script')
    <script>
        (function($) {
            "use strict";
            $(".langSel").on("change", function() {
                window.location.href = "{{ route('home') }}/change/" + $(this).val();
            });

            $('.language_switcher > .language_switcher__caption').on('click', function() {
                $(this).parent().toggleClass('open');
            });

            $(document).on('keyup', function(evt) {
                if ((evt.keyCode || evt.which) === 27) {
                    $('.language_switcher').removeClass('open');
                }
            });

            $(document).on('click', function(evt) {
                if ($(evt.target).closest(".language_switcher > .language_switcher__caption").length === 0) {
                    $('.language_switcher').removeClass('open');
                }
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        /* language */
        .language_switcher {
            position: relative;
            padding-right: 20px;
            min-width: max-content;
        }

        @media(max-width: 1199px) {
            .language_switcher {
                padding-block: 6px;
                display: inline-flex;
                margin-bottom: 24px;
                max-width: max-content;

            }

            .language_switcher_wrapper {
                flex: 1;
                text-align: right;
            }
        }

        .language_switcher::after {
            font-family: 'Line Awesome Free';
            content: "\f107";
            font-weight: 900;
            font-size: 14px;
            position: absolute;
            margin: 0;
            color: white;
            top: 50%;
            right: 0;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            transition: all ease 350ms;
            -webkit-transition: all ease 350ms;
            -moz-transition: all ease 350ms;
        }

        .language_switcher.open:after {
            -webkit-transform: translateY(-50%) rotate(180deg);
            transform: translateY(-50%) rotate(180deg);
        }

        .language_switcher__caption {
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: nowrap;
        }

        .language_switcher__caption .icon {
            position: relative;
            height: 20px;
            width: 20px;
            display: flex;
        }

        .language_switcher__caption .icon img {
            height: 100%;
            width: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .language_switcher__caption .text {
            font-size: 0.875rem;
            font-weight: 500;
            flex: 1;
            color: white;
            line-height: 1;
        }

        .language_switcher__list {
            width: 100px;
            border-radius: 4px;
            padding: 0;
            max-height: 105px;
            overflow-y: auto !important;
            background: #fff;
            -webkit-box-shadow: 0px 12px 24px rgba(21, 18, 51, 0.13);
            opacity: 0;
            overflow: hidden;
            -webkit-transition: all 0.15s cubic-bezier(0.25, 0, 0.25, 1.75),
                opacity 0.1s linear;
            transition: all 0.15s cubic-bezier(0.25, 0, 0.25, 1.75), opacity 0.1s linear;
            -webkit-transform: scale(0.85);
            transform: scale(0.85);
            -webkit-transform-origin: 50% 0;
            transform-origin: 50% 0;
            position: absolute;
            top: calc(100% + 18px);
            z-index: -1;
            visibility: hidden;
            border: 1px solid rgb(0 0 0 / 10%);
        }

        .language_switcher__list::-webkit-scrollbar-track {
            border-radius: 3px;
            background-color: hsl(var(--base) / 0.3);
        }

        .language_switcher__list::-webkit-scrollbar {
            width: 3px;
        }

        .language_switcher__list::-webkit-scrollbar-thumb {
            border-radius: 3px;
            background-color: hsl(var(--base) / 0.8);
        }

        .language_switcher__list .text {
            font-size: 0.875rem;
            font-weight: 500;
            color: black;
        }

        .language_switcher.open .language_switcher__list {
            -webkit-transform: scale(1);
            transform: scale(1);
            opacity: 1;
            z-index: 1;
            visibility: visible;
        }

        .language_switcher__item a {
            cursor: pointer;
            padding: 5px;
            border-bottom: 1px solid hsl(var(--heading-color) / 0.2);
            display: flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .language_switcher__item img {
            height: 20px;
            width: 20px;
            display: block;
            border-radius: 50%;
        }

        .language_switcher__item:last-of-type {
            border-bottom: 0;
        }

        .language_switcher__item.selected {
            background: rgba(36, 60, 187, 0.02);
            pointer-events: none;
        }
    </style>
@endpush
