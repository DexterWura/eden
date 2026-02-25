@extends($activeTemplate . 'layouts.frontend')
@section('content')
    {{-- Calculator Section --}}
    <section class="section">
        <div class="container">
            <div class="row g-4">
                {{-- Input Form --}}
                <div class="col-lg-5">
                    <div class="card custom--card earnings-form-card">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="las la-coins text-primary me-2"></i>
                                @lang('Enter Sale Details')
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="earningsForm">
                                {{-- Sale Price --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Expected Sale Price')</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light">{{ gs('cur_sym') }}</span>
                                        <input type="number" class="form-control form--control" id="salePrice" 
                                               name="sale_price" placeholder="e.g. 50000" min="100" step="1" required>
                                    </div>
                                    <small class="text-muted">@lang('The price you expect to sell your business for')</small>
                                </div>

                                {{-- Quick Price Buttons --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Quick Select')</label>
                                    <div class="quick-price-buttons">
                                        @foreach($priceTiers as $tier)
                                            <button type="button" class="btn btn-outline-secondary btn-sm quick-price-btn" 
                                                    data-price="{{ $tier }}">
                                                {{ gs('cur_sym') }}{{ number_format($tier) }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Payout Method Selection --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('How do you want to receive payment?')</label>
                                    <div class="payout-method-options">
                                        <label class="payout-option active" data-method="system">
                                            <input type="radio" name="payout_method" value="system" checked>
                                            <div class="payout-option-content">
                                                <div class="payout-icon">
                                                    <i class="las la-wallet"></i>
                                                </div>
                                                <div class="payout-details">
                                                    <strong>@lang('System Wallet')</strong>
                                                    <small>@lang('Receive funds in your platform wallet')</small>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="payout-option" data-method="direct">
                                            <input type="radio" name="payout_method" value="direct">
                                            <div class="payout-option-content">
                                                <div class="payout-icon">
                                                    <i class="las la-university"></i>
                                                </div>
                                                <div class="payout-details">
                                                    <strong>@lang('Direct Payout')</strong>
                                                    <small>@lang('Receive funds directly (bank, PayPal, etc.)')</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Fee Structure Display --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Applicable Fees')</label>
                                    
                                    {{-- System Wallet Fees --}}
                                    <div class="fee-structure-display" id="systemWalletFeesDisplay">
                                        @if(count($systemWalletFees['seller']) > 0)
                                            <div class="fee-section mb-3">
                                                <small class="text-muted d-block mb-2">
                                                    <i class="las la-user-tag me-1"></i>@lang('Seller Fees')
                                                </small>
                                                @foreach($systemWalletFees['seller'] as $fee)
                                                    <div class="fee-item">
                                                        <i class="{{ $fee['icon'] }} me-2 text-warning"></i>
                                                        <span>{{ $fee['name'] }}</span>
                                                        <span class="fee-rate">
                                                            @if($fee['percent'] > 0){{ $fee['percent'] }}%@endif
                                                            @if($fee['percent'] > 0 && $fee['fixed'] > 0)+@endif
                                                            @if($fee['fixed'] > 0){{ gs('cur_sym') }}{{ number_format($fee['fixed'], 2) }}@endif
                                                            @if($fee['cap'] > 0)<small class="text-muted">(max {{ gs('cur_sym') }}{{ number_format($fee['cap'], 2) }})</small>@endif
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-3 py-2">
                                                <i class="las la-check-circle me-1"></i>
                                                <small>@lang('No seller fees - you keep 100%!')</small>
                                            </div>
                                        @endif
                                        
                                        @if(count($systemWalletFees['buyer']) > 0)
                                            <div class="fee-section">
                                                <small class="text-muted d-block mb-2">
                                                    <i class="las la-user me-1"></i>@lang('Buyer Fees') <span class="badge bg-info">@lang('Paid by buyer')</span>
                                                </small>
                                                @foreach($systemWalletFees['buyer'] as $fee)
                                                    <div class="fee-item">
                                                        <i class="{{ $fee['icon'] }} me-2 text-info"></i>
                                                        <span>{{ $fee['name'] }}</span>
                                                        <span class="fee-rate">
                                                            @if($fee['percent'] > 0){{ $fee['percent'] }}%@endif
                                                            @if($fee['percent'] > 0 && $fee['fixed'] > 0)+@endif
                                                            @if($fee['fixed'] > 0){{ gs('cur_sym') }}{{ number_format($fee['fixed'], 2) }}@endif
                                                            @if($fee['cap'] > 0)<small class="text-muted">(max {{ gs('cur_sym') }}{{ number_format($fee['cap'], 2) }})</small>@endif
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Direct Payout Fees --}}
                                    <div class="fee-structure-display d-none" id="directPayoutFeesDisplay">
                                        @if(count($directPayoutFeesData['seller']) > 0)
                                            <div class="fee-section mb-3">
                                                <small class="text-muted d-block mb-2">
                                                    <i class="las la-user-tag me-1"></i>@lang('Seller Fees') <span class="badge bg-warning text-dark">@lang('Paid upfront')</span>
                                                </small>
                                                @foreach($directPayoutFeesData['seller'] as $fee)
                                                    <div class="fee-item">
                                                        <i class="{{ $fee['icon'] }} me-2 text-warning"></i>
                                                        <span>{{ $fee['name'] }}</span>
                                                        <span class="fee-rate">
                                                            @if($fee['percent'] > 0){{ $fee['percent'] }}%@endif
                                                            @if($fee['percent'] > 0 && $fee['fixed'] > 0)+@endif
                                                            @if($fee['fixed'] > 0){{ gs('cur_sym') }}{{ number_format($fee['fixed'], 2) }}@endif
                                                            @if($fee['cap'] > 0)<small class="text-muted">(max {{ gs('cur_sym') }}{{ number_format($fee['cap'], 2) }})</small>@endif
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-3 py-2">
                                                <i class="las la-check-circle me-1"></i>
                                                <small>@lang('No direct payout fees configured!')</small>
                                            </div>
                                        @endif
                                        
                                        <div class="alert alert-info mb-0 py-2">
                                            <i class="las la-info-circle me-1"></i>
                                            <small>@lang('No buyer fees - buyer pays you directly outside the platform.')</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Calculate Button --}}
                                <button type="submit" class="btn btn--base w-100 btn--lg" id="calculateBtn">
                                    <i class="las la-calculator me-2"></i>
                                    @lang('Calculate Earnings')
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Fee Info Card --}}
                    <div class="card custom--card mt-4">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="las la-question-circle text-primary me-2"></i>
                                @lang('Payout Methods Explained')
                            </h6>
                            <div class="payout-info">
                                <div class="payout-info-item mb-3">
                                    <strong><i class="las la-wallet me-2 text-primary"></i>@lang('System Wallet')</strong>
                                    <p class="text-muted small mb-0">
                                        @lang('Funds are held in escrow and released to your platform wallet after successful sale. Buyer pays escrow fee. You can withdraw anytime.')
                                    </p>
                                </div>
                                <div class="payout-info-item">
                                    <strong><i class="las la-university me-2 text-primary"></i>@lang('Direct Payout')</strong>
                                    <p class="text-muted small mb-0">
                                        @lang('Buyer pays you directly via your preferred method (bank, PayPal, crypto, etc.). You pay an upfront listing fee. Faster access to funds.')
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Results Section --}}
                <div class="col-lg-7">
                    {{-- Initial State --}}
                    <div class="card custom--card earnings-result-card h-100" id="initialState">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5">
                            <div class="result-icon-placeholder mb-4">
                                <i class="las la-hand-holding-usd"></i>
                            </div>
                            <h4 class="text-muted mb-3">@lang('Your Earnings Will Appear Here')</h4>
                            <p class="text-muted mb-0">
                                @lang('Enter your expected sale price and select a payout method to see exactly how much you\'ll receive.')
                            </p>
                        </div>
                    </div>

                    {{-- Results State (Hidden Initially) --}}
                    <div class="card custom--card earnings-result-card d-none" id="resultsState">
                        <div class="card-header bg-success text-white text-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="las la-check-circle me-2"></i>
                                <span id="resultsTitle">@lang('Your Net Earnings')</span>
                            </h5>
                            <small id="payoutMethodLabel" class="text-white-50"></small>
                        </div>
                        <div class="card-body">
                            {{-- Main Earnings Display --}}
                            <div class="earnings-display text-center mb-4">
                                <div class="sale-price-label">@lang('Sale Price')</div>
                                <div class="sale-price-amount" id="displaySalePrice">{{ gs('cur_sym') }}0</div>
                                <div class="earnings-arrow">
                                    <i class="las la-arrow-down"></i>
                                </div>
                                <div class="net-earnings-label">@lang('You Receive')</div>
                                <div class="net-earnings-amount" id="displayNetEarnings">{{ gs('cur_sym') }}0</div>
                                <div class="earnings-percentage" id="displayPercentage">
                                    @lang('(0% of sale price)')
                                </div>
                            </div>

                            {{-- Fee Breakdown --}}
                            <div class="fee-breakdown mb-4">
                                <h6 class="breakdown-title mb-3">
                                    <i class="las la-receipt me-2"></i>@lang('Fee Breakdown')
                                </h6>
                                <div class="breakdown-table">
                                    <div class="breakdown-row">
                                        <span class="breakdown-label">
                                            <i class="las la-tag me-2 text-muted"></i>@lang('Sale Price')
                                        </span>
                                        <span class="breakdown-value" id="breakdownSalePrice">{{ gs('cur_sym') }}0</span>
                                    </div>
                                    <div id="sellerFeesBreakdown">
                                        {{-- Populated by JavaScript --}}
                                    </div>
                                    <div class="breakdown-row total-row">
                                        <span class="breakdown-label">
                                            <i class="las la-minus-circle me-2 text-muted"></i>@lang('Total Seller Fees')
                                            <span class="fee-timing-badge" id="feeTimingBadge"></span>
                                        </span>
                                        <span class="breakdown-value text-danger" id="breakdownTotalSellerFees">-{{ gs('cur_sym') }}0</span>
                                    </div>
                                    <div class="breakdown-row net-row">
                                        <span class="breakdown-label">
                                            <i class="las la-wallet me-2"></i><strong>@lang('Net Earnings')</strong>
                                        </span>
                                        <span class="breakdown-value text-success" id="breakdownNetEarnings">{{ gs('cur_sym') }}0</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Buyer Fees Info (Only for System Wallet) --}}
                            <div class="buyer-fees-info mb-4 d-none" id="buyerFeesSection">
                                <h6 class="breakdown-title mb-3">
                                    <i class="las la-info-circle me-2"></i>@lang('Buyer Pays Separately')
                                </h6>
                                <div class="alert alert-info mb-0">
                                    <div id="buyerFeesBreakdown">
                                        {{-- Populated by JavaScript --}}
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        @lang('These fees are paid by the buyer and do not affect your earnings.')
                                    </small>
                                </div>
                            </div>

                            {{-- Direct Payout Note (Only for Direct Payout) --}}
                            <div class="direct-payout-note mb-4 d-none" id="directPayoutNote">
                                <div class="alert alert-warning mb-0">
                                    <i class="las la-exclamation-triangle me-2"></i>
                                    <strong>@lang('Important:')</strong>
                                    @lang('The listing fee is charged upfront when you create your listing. The buyer will pay you directly via your provided payment link.')
                                </div>
                            </div>

                            {{-- CTAs --}}
                            <div class="earnings-ctas">
                                <a href="{{ route('user.listing.create') }}" class="btn btn--base w-100 btn--lg mb-3">
                                    <i class="las la-rocket me-2"></i>
                                    @lang('List Your Business - It\'s Free')
                                </a>
                                <a href="{{ route('tools.valuation') }}" class="btn btn-outline-secondary w-100">
                                    <i class="las la-calculator me-2"></i>
                                    @lang('Calculate Your Business Value')
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <small class="text-muted">
                                <i class="las la-info-circle me-1"></i>
                                @lang('This is an estimate. Actual fees may vary based on sale terms.')
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTA Banner --}}
            <div class="row mt-5">
                <div class="col-12">
                    <div class="cta-banner">
                        <div class="row align-items-center">
                            <div class="col-lg-8 mb-3 mb-lg-0">
                                <h4 class="text-white mb-2">@lang('Ready to Sell Your Business?')</h4>
                                <p class="text-white-50 mb-0">
                                    @lang('Choose the payout method that works best for you. List your business today!')
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
    /* Quick Price Buttons */
    .quick-price-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .quick-price-btn {
        font-size: 0.8rem;
        padding: 5px 12px;
        border-radius: 20px;
        transition: all 0.2s ease;
    }
    .quick-price-btn:hover,
    .quick-price-btn.active {
        background: hsl(var(--base));
        border-color: hsl(var(--base));
        color: #fff;
    }

    /* Payout Method Options */
    .payout-method-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .payout-option {
        display: block;
        cursor: pointer;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.2s ease;
        margin: 0;
    }
    .payout-option:hover {
        border-color: hsl(var(--base)/0.5);
    }
    .payout-option.active {
        border-color: hsl(var(--base));
        background: hsl(var(--base)/0.05);
    }
    .payout-option input {
        display: none;
    }
    .payout-option-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .payout-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: hsl(var(--base)/0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .payout-icon i {
        font-size: 1.5rem;
        color: hsl(var(--base));
    }
    .payout-option.active .payout-icon {
        background: hsl(var(--base));
    }
    .payout-option.active .payout-icon i {
        color: #fff;
    }
    .payout-details {
        display: flex;
        flex-direction: column;
    }
    .payout-details strong {
        font-size: 0.95rem;
    }
    .payout-details small {
        color: #6c757d;
        font-size: 0.8rem;
    }

    /* Fee Structure Display */
    .fee-structure-display {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
    }
    .fee-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.9rem;
    }
    .fee-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .fee-item span:first-of-type {
        flex: 1;
    }
    .fee-rate {
        font-weight: 600;
        color: hsl(var(--base));
    }

    /* Results Display */
    .earnings-display {
        padding: 30px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
    }
    .sale-price-label,
    .net-earnings-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .sale-price-amount {
        font-size: 1.5rem;
        font-weight: 600;
        color: #495057;
    }
    .earnings-arrow {
        margin: 15px 0;
        font-size: 1.5rem;
        color: hsl(var(--base));
    }
    .net-earnings-amount {
        font-size: 2.5rem;
        font-weight: 700;
        color: hsl(var(--success));
    }
    .earnings-percentage {
        font-size: 0.9rem;
        color: #6c757d;
    }

    /* Breakdown Table */
    .breakdown-table {
        background: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
    }
    .breakdown-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
    }
    .breakdown-row:last-child {
        border-bottom: none;
    }
    .breakdown-label {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        flex-wrap: wrap;
        gap: 5px;
    }
    .fee-percentage {
        font-size: 0.75rem;
        color: #6c757d;
        margin-left: 5px;
    }
    .fee-timing-badge {
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 5px;
    }
    .breakdown-value {
        font-weight: 600;
    }
    .total-row {
        background: #fff3cd;
    }
    .net-row {
        background: #d4edda;
    }
    .net-row .breakdown-value {
        font-size: 1.1rem;
    }
    .fee-row {
        background: #fff;
    }

    /* Result Icon */
    .result-icon-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, hsl(var(--base)/0.1) 0%, hsl(var(--base)/0.2) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .result-icon-placeholder i {
        font-size: 4rem;
        color: hsl(var(--base));
    }

    /* CTA Banner */
    .cta-banner {
        background: linear-gradient(135deg, hsl(var(--base)) 0%, hsl(var(--base)/0.8) 100%);
        padding: 40px;
        border-radius: 16px;
    }

    /* Payout Info */
    .payout-info-item {
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    .payout-info-item:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }
</style>
@endpush

@push('script')
    <script>
        // Pass fee configuration to JavaScript
        const currencySymbol = '{{ gs("cur_sym") }}';
        const currencyCode = '{{ gs("cur_text") }}';
        
        // System wallet fees
        const systemWalletFees = {
            buyer: @json($systemWalletFees['buyer']),
            seller: @json($systemWalletFees['seller'])
        };
        
        // Direct payout fees
        const directPayoutFees = {
            buyer: @json($directPayoutFeesData['buyer']),
            seller: @json($directPayoutFeesData['seller'])
        };
        
        // Tiered charges (for legacy system)
        const tieredCharges = @json($tieredCharges);
        
        // Fee sources
        const systemFeeSource = '{{ $systemFeeSource }}';
        const directFeeSource = '{{ $directFeeSource }}';
    </script>
    <script src="{{ asset($activeTemplateTrue . 'js/seller-earnings-calculator.js') }}?v={{ time() }}"></script>
@endpush
