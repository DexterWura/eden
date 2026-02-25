<div class="sidebar bg--dark">
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{ route('home') }}" class="sidebar__main-logo"><img src="{{siteLogo()}}" alt="image"></a>
        </div>
        <div class="sidebar__menu-wrapper">
            <ul class="sidebar__menu">
                <li class="sidebar-menu-item {{ menuActive('user.home') }}">
                    <a href="{{ route('user.home') }}" class="nav-link">
                        <i class="menu-icon las la-home"></i>
                        <span class="menu-title">@lang('Dashboard')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown {{ menuActive('user.listing*', 3) }}">
                    <a href="javascript:void(0)" class="{{ menuActive('user.listing*', 3) }}">
                        <i class="menu-icon las la-store"></i>
                        <span class="menu-title">@lang('My Listings')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('user.listing*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('user.listing.create') }}">
                                <a href="{{ route('user.listing.create') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Create Listing')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('user.listing.index') }}">
                                <a href="{{ route('user.listing.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('All Listings')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item {{ menuActive('user.bid*') }}">
                    <a href="{{ route('user.bid.index') }}" class="nav-link">
                        <i class="menu-icon las la-gavel"></i>
                        <span class="menu-title">@lang('My Bids')</span>
                        @if(!empty($wonAuctionsCount) && $wonAuctionsCount > 0)
                            <span class="menu-badge menu-badge-level-one bg--warning ms-auto" title="@lang('You have won auctions')">
                                <i class="fas fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown {{ menuActive('user.offer*', 3) }}">
                    <a href="javascript:void(0)" class="{{ menuActive('user.offer*', 3) }}">
                        <i class="menu-icon las la-handshake"></i>
                        <span class="menu-title">@lang('My Offers')</span>
                        @if(!empty($pendingReceivedOffersCount) && $pendingReceivedOffersCount > 0)
                            <span class="menu-badge menu-badge-level-one bg--warning ms-auto" title="@lang('You have offers to review')">
                                <i class="fas fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive('user.offer*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('user.offer.index') }}">
                                <a href="{{ route('user.offer.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Offers Made')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('user.offer.received') }}">
                                <a href="{{ route('user.offer.received') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Received Offers')</span>
                                    @if(!empty($pendingReceivedOffersCount) && $pendingReceivedOffersCount > 0)
                                        <span class="menu-badge bg--info ms-auto">{{ $pendingReceivedOffersCount }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item {{ menuActive('user.watchlist*') }}">
                    <a href="{{ route('user.watchlist.index') }}" class="nav-link">
                        <i class="menu-icon las la-heart"></i>
                        <span class="menu-title">@lang('Watchlist')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('user.escrow*') }}">
                    <a href="{{ route('user.escrow.index') }}" class="nav-link">
                        <i class="menu-icon las la-shopping-cart"></i>
                        <span class="menu-title">@lang('My Purchases')</span>
                        @if(!empty($pendingDealsCount) && $pendingDealsCount > 0)
                            <span class="menu-badge menu-badge-level-one bg--warning ms-auto" title="@lang('You have pending deals')">
                                <i class="fas fa-exclamation"></i>
                            </span>
                        @endif
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('user.transactions') }}">
                    <a href="{{ route('user.transactions') }}" class="nav-link">
                        <i class="menu-icon las la-exchange-alt"></i>
                        <span class="menu-title">@lang('Transactions')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown {{ menuActive('user.deposit*', 3) }}">
                    <a href="javascript:void(0)" class="{{ menuActive('user.deposit*', 3) }}">
                        <i class="menu-icon las la-file-invoice-dollar"></i>
                        <span class="menu-title">@lang('Deposits')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('user.deposit*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('user.deposit.index') }}">
                                <a href="{{ route('user.deposit.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Deposit Now')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('user.deposit.history') }}">
                                <a href="{{ route('user.deposit.history') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Deposit History')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown {{ menuActive('user.withdraw*', 3) }}">
                    <a href="javascript:void(0)" class="{{ menuActive('user.withdraw*', 3) }}">
                        <i class="menu-icon la la-bank"></i>
                        <span class="menu-title">@lang('Withdrawals')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('user.withdraw*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('user.withdraw') }}">
                                <a href="{{ route('user.withdraw') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Withdraw Money')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('user.withdraw.history') }}">
                                <a href="{{ route('user.withdraw.history') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Withdrawal History')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item {{ menuActive('user.profile*') }}">
                    <a href="{{ route('user.profile.setting') }}" class="nav-link">
                        <i class="menu-icon las la-user-circle"></i>
                        <span class="menu-title">@lang('Profile Settings')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item {{ menuActive('user.notification.settings') }}">
                    <a href="{{ route('user.notification.settings') }}" class="nav-link">
                        <i class="menu-icon las la-bell"></i>
                        <span class="menu-title">@lang('Notification Settings')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown {{ menuActive('user.twofactor,user.kyc*', 3) }}">
                    <a href="javascript:void(0)" class="{{ menuActive('user.twofactor,user.kyc*', 3) }}">
                        <i class="menu-icon las la-shield-alt"></i>
                        <span class="menu-title">@lang('Security')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive('user.twofactor,user.kyc*', 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('user.twofactor') }}">
                                <a href="{{ route('user.twofactor') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('2FA Security')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('user.kyc*') }}">
                                <a href="{{ route('user.kyc.form') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('KYC Verification')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item {{ menuActive('ticket*') }}">
                    <a href="{{ route('ticket.index') }}" class="nav-link">
                        <i class="menu-icon la la-ticket"></i>
                        <span class="menu-title">@lang('Support')</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="version-info text-center text-uppercase">
            <span class="text--primary">{{__(gs('site_name'))}}</span>
        </div>
    </div>
</div>
<!-- sidebar end -->

@push('script')
    <script>
        if($('li').hasClass('active')){
            $('.sidebar__menu-wrapper').animate({
                scrollTop: eval($(".active").offset().top - 320)
            },500);
        }
    </script>
@endpush

