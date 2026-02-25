@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    @php
        $kycContent = getContent('kyc.content', true);
    @endphp

    {{-- KYC Alerts --}}
    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="alert-heading text--danger m-0">
                            <i class="las la-exclamation-circle me-2"></i>
                            @lang('KYC Verification Required')
                        </h4>
                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">
                            @lang('Show Reason')
                        </button>
                    </div>
                    <hr>
                    <p class="mb-0">
                        {{ __(@$kycContent->data_values->reject) }}
                        <a href="{{ route('user.kyc.form') }}">
                            @lang('Click Here to Re-submit Documents')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->kv == Status::KYC_UNVERIFIED)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info mb-3">
                    <h4 class="alert-heading text--danger">
                        <i class="las la-info-circle me-2"></i>
                        @lang('KYC Verification Required')
                    </h4>
                    <hr>
                    <p class="mb-0">
                        {{ __(@$kycContent->data_values->required) }}
                        <a href="{{ route('user.kyc.form') }}">
                            @lang('Click Here to Verify')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @elseif(auth()->user()->kv == Status::KYC_PENDING)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning mb-3">
                    <h4 class="alert-heading text--warning">
                        <i class="las la-clock me-2"></i>
                        @lang('KYC Verification Pending')
                    </h4>
                    <hr>
                    <p class="mb-0">
                        {{ __(@$kycContent->data_values->pending) }}
                        <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Pending Escrow Actions Reminders --}}
    @if(isset($pendingActions) && count($pendingActions) > 0)
        @foreach($pendingActions as $action)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-{{ $action['priority'] == 'high' ? 'danger' : 'warning' }} mb-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="flex-grow-1">
                                <h4 class="alert-heading text--{{ $action['priority'] == 'high' ? 'danger' : 'warning' }} m-0">
                                    @if($action['type'] == 'escrow_accept_buyer')
                                        <i class="las la-handshake me-2"></i>
                                        @lang('Action Required: Accept Escrow')
                                    @elseif($action['type'] == 'escrow_accept_seller')
                                        <i class="las la-handshake me-2"></i>
                                        @lang('Action Required: Accept Escrow')
                                    @elseif($action['type'] == 'escrow_payment_required')
                                        <i class="las la-credit-card me-2"></i>
                                        @lang('Payment Required')
                                    @elseif($action['type'] == 'milestones_pending_approval' || $action['type'] == 'milestones_pending_approval_seller')
                                        <i class="las la-tasks me-2"></i>
                                        @lang('Milestones Pending Approval')
                                    @elseif($action['type'] == 'milestones_ready_payment')
                                        <i class="las la-dollar-sign me-2"></i>
                                        @lang('Milestones Ready for Payment')
                                    @else
                                        <i class="las la-exclamation-triangle me-2"></i>
                                        @lang('Action Required')
                                    @endif
                                </h4>
                            </div>
                        </div>
                        <hr>
                        <p class="mb-2">
                            <strong>{{ $action['listing_title'] }}</strong>
                        </p>
                        <p class="mb-0">
                            {{ $action['message'] }}
                            <a href="{{ $action['link'] }}" class="fw-bold">
                                {{ $action['linkText'] }} <i class="las la-arrow-right ms-1"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Affiliate referral link and stats (only when enabled by admin) --}}
    @if(!empty($affiliateEnabled) && !empty($referralLink))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card custom--card border-0 shadow-sm">
                <div class="card-body py-4 overflow-hidden">
                    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-3 mb-3">
                        <div class="d-flex align-items-center flex-shrink-0">
                            <span class="rounded-circle bg--base bg-opacity-10 p-2 me-2">
                                <i class="las la-user-friends text--base fs-5"></i>
                            </span>
                            <div class="min-w-0">
                                <h6 class="mb-0">@lang('Your Referral Link')</h6>
                                <small class="text-muted">@lang('Earn') {{ showAmount(gs('affiliate_signup_amount') ?? 0) }} @lang('per signup')</small>
                            </div>
                        </div>
                        <div class="d-flex flex-grow-1 flex-md-grow-0 align-items-center gap-2 ms-md-auto min-w-0 w-100">
                            <input type="text" class="form-control form-control-sm form--control referral-link-input border flex-grow-1" value="{{ $referralLink }}" readonly style="min-width: 0; background-color: #f0f2f5; color: #1a1d21;">
                            <button type="button" class="btn btn--base btn-sm copy-referral-link flex-shrink-0" data-copy="{{ $referralLink }}">
                                <i class="las la-copy"></i> @lang('Copy')
                            </button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-4 pt-3 border-top">
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="text-muted small">@lang('Referred signups'):</span>
                            <span class="fw-bold">{{ number_format($referralCount ?? 0) }}</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="text-muted small">@lang('Total earned from referrals'):</span>
                            <span class="fw-bold text--base">{{ showAmount($referralEarned ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none d-flex align-items-center gap-1 referral-users-toggle" data-bs-toggle="collapse" data-bs-target="#referredUsersCollapse" aria-expanded="false" aria-controls="referredUsersCollapse">
                            <i class="las la-chevron-down referral-users-chevron"></i>
                            <span>@lang('Recent referred users')</span>
                            @if(!empty($referralCount) && $referralCount > 0)
                                <span class="badge bg--base ms-1">{{ $referralCount }}</span>
                            @endif
                        </button>
                        <div class="collapse mt-2" id="referredUsersCollapse">
                            <div class="referral-users-placeholder text-muted small py-2">
                                <span class="referral-users-loading d-none">@lang('Loading...')</span>
                                <span class="referral-users-empty d-none">@lang('No referred users yet.')</span>
                                <div class="referral-users-content d-none">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-muted fw-normal small">@lang('Username')</th>
                                                    <th class="text-muted fw-normal small">@lang('Signed up')</th>
                                                </tr>
                                            </thead>
                                            <tbody class="referral-users-tbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Financial Overview --}}
    <div class="row gy-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.deposit.index') }}" icon="las la-wallet" title="Balance" value="{{ showAmount($data['balance']) }}"
                bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.deposit.history', 'pending') }}" icon="las la-pause-circle" title="Pending Deposits"
                value="{{ $data['pendingDeposit'] }}" bg="warning" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.withdraw.history', 'pending') }}" icon="las la-pause-circle" title="Pending Withdrawals"
                value="{{ $data['pendingWithdrawals'] }}" bg="danger" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.transactions') }}" icon="las la-exchange-alt" title="Transactions"
                value="{{ $transactions->count() }}" bg="info" />
        </div>
    </div>

    {{-- Marketplace Statistics (Primary) --}}
    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-store" link="{{ route('user.listing.index') }}" style="7" type="2"
                title="My Listings" value="{{ number_format($data['my_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-check-circle" link="{{ route('user.listing.index', ['status' => Status::LISTING_ACTIVE]) }}" style="7" type="2"
                title="Active Listings" value="{{ number_format($data['active_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-check-double" link="{{ route('user.listing.index', ['status' => Status::LISTING_SOLD]) }}" style="7" type="2"
                title="Sold Listings" value="{{ number_format($data['sold_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-dollar-sign" link="{{ route('user.listing.index', ['status' => Status::LISTING_SOLD]) }}" style="7" type="2"
                title="Total Sales Value" value="{{ showAmount($data['total_sales_value']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-gavel" link="{{ route('user.bid.index') }}" style="7" type="2"
                title="My Bids" value="{{ number_format($data['my_bids']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="warning" icon="las la-trophy" link="{{ route('user.bid.index', ['status' => Status::BID_WINNING]) }}" style="7" type="2"
                title="Winning Bids" value="{{ number_format($data['winning_bids']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-handshake" link="{{ route('user.offer.index') }}" style="7" type="2"
                title="My Offers" value="{{ number_format($data['my_offers']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="danger" icon="las la-heart" link="{{ route('user.watchlist.index') }}" style="7" type="2"
                title="Watchlist Items" value="{{ number_format($data['watchlist_items']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-eye" link="{{ route('user.listing.index') }}" style="7" type="2"
                title="Total Views" value="{{ number_format($data['total_listing_views']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-file-signature" link="{{ route('user.nda.index') }}" style="7" type="2"
                title="Signed NDAs" value="{{ number_format($data['signed_ndas']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-shopping-cart" link="{{ route('user.escrow.index', 'accepted') }}" style="7" type="2"
                title="Active Escrows" value="{{ number_format($data['active_escrows']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-check-double" link="{{ route('user.escrow.index', 'completed') }}" style="7" type="2"
                title="Completed Escrows" value="{{ number_format($data['completed_escrows']) }}" />
        </div>
    </div>

    {{-- Latest Transactions --}}
    <div class="row mb-none-30 mt-30">
        <div class="col-xl-12 mb-30">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Transaction ID')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Details')</th>
                                    <th>@lang('Balance')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $trx)
                                    <tr>
                                        <td>
                                            <span>{{ showDateTime($trx->created_at, 'd M, Y h:i A') }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $trx->trx }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold @if($trx->trx_type == '+') text--success @else text--danger @endif">
                                                {{ $trx->trx_type == '+' ? '+' : '-' }}{{ showAmount($trx->amount) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span>{{ __($trx->details) }}</span>
                                        </td>
                                        <td>
                                            <span>{{ showAmount($trx->post_balance) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($affiliateEnabled) && !empty($referralLink))
    @push('script')
    <script>
        (function () {
            document.querySelectorAll('.copy-referral-link').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var url = this.getAttribute('data-copy');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(function () {
                            var old = btn.innerHTML;
                            btn.innerHTML = '<i class="las la-check"></i> {{ __("Copied!") }}';
                            setTimeout(function () { btn.innerHTML = old; }, 2000);
                        });
                    } else {
                        var inp = document.querySelector('.referral-link-input');
                        if (inp) { inp.select(); document.execCommand('copy'); }
                    }
                });
            });

            var referredUsersLoaded = false;
            var collapseEl = document.getElementById('referredUsersCollapse');
            var toggleBtn = document.querySelector('.referral-users-toggle');
            var chevronEl = document.querySelector('.referral-users-chevron');
            if (collapseEl && toggleBtn) {
                collapseEl.addEventListener('show.bs.collapse', function () {
                    if (chevronEl) chevronEl.classList.replace('la-chevron-down', 'la-chevron-up');
                    if (referredUsersLoaded) return;
                    var loading = document.querySelector('.referral-users-loading');
                    var empty = document.querySelector('.referral-users-empty');
                    var content = document.querySelector('.referral-users-content');
                    var tbody = document.querySelector('.referral-users-tbody');
                    if (loading) loading.classList.remove('d-none');
                    if (empty) empty.classList.add('d-none');
                    if (content) content.classList.add('d-none');
                    fetch('{{ route("user.referral.referred.users") }}')
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            referredUsersLoaded = true;
                            if (loading) loading.classList.add('d-none');
                            if (data.users && data.users.length > 0) {
                                if (content) content.classList.remove('d-none');
                                if (tbody) {
                                    tbody.innerHTML = data.users.map(function (u) {
                                        return '<tr><td>' + (u.username || '') + '</td><td class="text-muted small">' + (u.signed_up || '') + '</td></tr>';
                                    }).join('');
                                }
                            } else {
                                if (empty) empty.classList.remove('d-none');
                            }
                        })
                        .catch(function () {
                            if (loading) loading.classList.add('d-none');
                            if (empty) { empty.classList.remove('d-none'); empty.textContent = '{{ __("Unable to load.") }}'; }
                        });
                });
                collapseEl.addEventListener('hide.bs.collapse', function () {
                    if (chevronEl) chevronEl.classList.replace('la-chevron-up', 'la-chevron-down');
                });
            }
        })();
    </script>
    @endpush
    @endif

    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
        <div class="modal fade custom--modal" id="kycRejectionReason">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="existModalLongTitle">@lang('KYC Document Rejection Reason')</h5>
                        <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <p class="py-3">{{ auth()->user()->kyc_rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
