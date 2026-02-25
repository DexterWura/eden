@extends($activeTemplate . 'layouts.frontend')
@section('content')
    {{-- Calculator Section --}}
    <section class="section">
        <div class="container">
            <div class="row g-4">
                {{-- Input Form --}}
                <div class="col-lg-6">
                    <div class="card custom--card valuation-form-card">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="las la-edit text-primary me-2"></i>
                                @lang('Enter Your Business Details')
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="valuationForm">
                                {{-- Business Type --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Business Type')</label>
                                    <select class="form-control form--control" id="businessType" name="business_type" required>
                                        <option value="">@lang('Select Business Type')</option>
                                        @foreach($businessTypes as $key => $type)
                                            <option value="{{ $key }}" 
                                                    data-base="{{ $type['baseMultiple'] }}" 
                                                    data-max="{{ $type['maxMultiple'] }}">
                                                {{ __($type['name']) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Monthly Revenue --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Monthly Revenue')</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ gs('cur_sym') }}</span>
                                        <input type="number" class="form-control form--control" id="monthlyRevenue" 
                                               name="monthly_revenue" placeholder="e.g. 5000" min="0" step="0.01" required>
                                    </div>
                                    <small class="text-muted">@lang('Average monthly revenue before expenses')</small>
                                </div>

                                {{-- Monthly Profit --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Monthly Profit')</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ gs('cur_sym') }}</span>
                                        <input type="number" class="form-control form--control" id="monthlyProfit" 
                                               name="monthly_profit" placeholder="e.g. 2000" min="0" step="0.01" required>
                                    </div>
                                    <small class="text-muted">@lang('Net profit after all expenses')</small>
                                </div>

                                {{-- Monthly Visitors --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Monthly Visitors')</label>
                                    <input type="number" class="form-control form--control" id="monthlyVisitors" 
                                           name="monthly_visitors" placeholder="e.g. 10000" min="0">
                                    <small class="text-muted">@lang('Approximate monthly unique visitors')</small>
                                </div>

                                {{-- Business Age --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Business Age')</label>
                                    <select class="form-control form--control" id="businessAge" name="business_age" required>
                                        @foreach($ageOptions as $value => $label)
                                            <option value="{{ $value }}">{{ __($label) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Monthly Growth --}}
                                <div class="form-group mb-4">
                                    <label class="form-label">@lang('Monthly Growth Rate')</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form--control" id="monthlyGrowth" 
                                               name="monthly_growth" placeholder="e.g. 10" min="-100" max="1000" step="0.1">
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                    <small class="text-muted">@lang('Average month-over-month growth')</small>
                                </div>

                                {{-- Recurring Revenue Toggle --}}
                                <div class="form-group mb-4">
                                    <label class="form-label d-block">@lang('Has Recurring Revenue?')</label>
                                    <div class="recurring-toggle">
                                        <label class="toggle-option">
                                            <input type="radio" name="has_recurring" value="1" id="recurringYes">
                                            <span class="toggle-label">
                                                <i class="las la-check-circle"></i> @lang('Yes')
                                            </span>
                                        </label>
                                        <label class="toggle-option">
                                            <input type="radio" name="has_recurring" value="0" id="recurringNo" checked>
                                            <span class="toggle-label">
                                                <i class="las la-times-circle"></i> @lang('No')
                                            </span>
                                        </label>
                                    </div>
                                    <small class="text-muted">@lang('Subscriptions, memberships, or retainer-based income')</small>
                                </div>

                                {{-- Calculate Button --}}
                                <button type="submit" class="btn btn--base w-100 btn--lg" id="calculateBtn">
                                    <i class="las la-calculator me-2"></i>
                                    @lang('Calculate Valuation')
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Results Section --}}
                <div class="col-lg-6">
                    {{-- Initial State --}}
                    <div class="card custom--card valuation-result-card h-100" id="initialState">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5">
                            <div class="result-icon-placeholder mb-4">
                                <i class="las la-chart-line"></i>
                            </div>
                            <h4 class="text-muted mb-3">@lang('Your Valuation Will Appear Here')</h4>
                            <p class="text-muted mb-0">
                                @lang('Fill in your business details and click "Calculate Valuation" to get an instant estimate.')
                            </p>
                        </div>
                    </div>

                    {{-- Results State (Hidden Initially) --}}
                    <div class="card custom--card valuation-result-card h-100 d-none" id="resultsState">
                        <div class="card-header bg-success text-white text-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="las la-check-circle me-2"></i>
                                @lang('Your Estimated Valuation')
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Valuation Range --}}
                            <div class="valuation-display text-center mb-4">
                                <div class="valuation-amount" id="valuationRange">
                                    {{ gs('cur_sym') }}0 - {{ gs('cur_sym') }}0
                                </div>
                                <p class="valuation-subtext text-muted" id="multipleRange">
                                    @lang('Based on 0x - 0x annual profit')
                                </p>
                            </div>

                            {{-- Breakdown --}}
                            <div class="valuation-breakdown mb-4">
                                <h6 class="breakdown-title mb-3">
                                    <i class="las la-list-ul me-2"></i>@lang('Valuation Breakdown')
                                </h6>
                                <ul class="breakdown-list" id="breakdownList">
                                    {{-- Populated by JavaScript --}}
                                </ul>
                            </div>

                            {{-- Annual Metrics --}}
                            <div class="annual-metrics mb-4">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="metric-box">
                                            <span class="metric-label">@lang('Annual Revenue')</span>
                                            <span class="metric-value" id="annualRevenue">{{ gs('cur_sym') }}0</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="metric-box">
                                            <span class="metric-label">@lang('Annual Profit')</span>
                                            <span class="metric-value" id="annualProfit">{{ gs('cur_sym') }}0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CTAs --}}
                            <div class="valuation-ctas">
                                <a href="{{ route('user.listing.create') }}" class="btn btn--base w-100 btn--lg mb-3">
                                    <i class="las la-rocket me-2"></i>
                                    @lang('List Your Business - It\'s Free')
                                </a>
                                <a href="{{ route('marketplace.browse') }}" class="btn btn-outline-secondary w-100">
                                    <i class="las la-search me-2"></i>
                                    @lang('Browse Similar Businesses')
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <small class="text-muted">
                                <i class="las la-info-circle me-1"></i>
                                @lang('This is an estimate based on industry averages. Actual sale price may vary.')
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Section --}}
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card custom--card">
                        <div class="card-body">
                            <h5 class="mb-4">
                                <i class="las la-info-circle text-primary me-2"></i>
                                @lang('How We Calculate Your Valuation')
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="las la-percentage"></i>
                                        </div>
                                        <h6>@lang('Industry Multiples')</h6>
                                        <p class="text-muted mb-0 small">
                                            @lang('We use standard profit multiples based on your business type (2.5x - 6x annual profit).')
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="las la-history"></i>
                                        </div>
                                        <h6>@lang('Business Age')</h6>
                                        <p class="text-muted mb-0 small">
                                            @lang('Older, established businesses command higher multiples due to proven stability.')
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="las la-chart-line"></i>
                                        </div>
                                        <h6>@lang('Growth Rate')</h6>
                                        <p class="text-muted mb-0 small">
                                            @lang('Growing businesses are more valuable. High growth adds to your multiple.')
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="las la-sync"></i>
                                        </div>
                                        <h6>@lang('Recurring Revenue')</h6>
                                        <p class="text-muted mb-0 small">
                                            @lang('Subscription or recurring income significantly increases business value.')
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                                    @lang('Join thousands of entrepreneurs who have successfully sold their online businesses on FLIPit.')
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

@push('script')
    <script src="{{ asset($activeTemplateTrue . 'js/valuation-calculator.js') }}?v={{ time() }}"></script>
    <script>
        // Initialize calculator with currency symbol
        const currencySymbol = '{{ gs("cur_sym") }}';
        const currencyCode = '{{ gs("cur_text") }}';
    </script>
@endpush
