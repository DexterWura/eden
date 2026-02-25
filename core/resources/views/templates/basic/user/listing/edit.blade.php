@extends($activeTemplate . 'user.layouts.app')
@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
                    <div class="card-header bg--base">
                        <h5 class="mb-0 text-white"><i class="las la-edit"></i> @lang('Edit Listing')</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.listing.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Basic Information -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-info-circle"></i> @lang('Basic Information')</h6>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label class="form-label">@lang('Title') <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('Business Type')</label>
                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">@lang('Category')</label>
                                    <select name="listing_category_id" class="form-select">
                                        <option value="">@lang('Select Category')</option>
                                        @foreach($listingCategories as $category)
                                            @if($category->business_type == $listing->business_type)
                                                <option value="{{ $category->id }}" @selected($listing->listing_category_id == $category->id)>
                                                    {{ $category->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">@lang('Tagline')</label>
                                    <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $listing->tagline) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">@lang('Description') <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="6" required>{{ old('description', $listing->description) }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Pricing -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-dollar-sign"></i> @lang('Pricing')</h6>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                @if($listing->sale_type === 'fixed_price')
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Asking Price') <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="asking_price" class="form-control" 
                                                   value="{{ old('asking_price', $listing->asking_price) }}" step="0.01" min="1" required>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Starting Bid') <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="starting_bid" class="form-control" 
                                                   value="{{ old('starting_bid', $listing->starting_bid) }}" step="0.01" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Reserve Price')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="reserve_price" class="form-control" 
                                                   value="{{ old('reserve_price', $listing->reserve_price) }}" step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Buy Now Price')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="buy_now_price" class="form-control" 
                                                   value="{{ old('buy_now_price', $listing->buy_now_price) }}" step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Bid Increment') <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="bid_increment" class="form-control" 
                                                   value="{{ old('bid_increment', $listing->bid_increment ?? 1) }}" step="0.01" min="1" required>
                                        </div>
                                        <small class="text-muted">@lang('Minimum amount by which a new bid must exceed the current highest bid.')</small>
                                    </div>
                                @endif
                            </div>

                            <!-- Payout Method -->
                            @php
                                $payoutMethod = old('payout_method', $listing->payout_method ?? 'system');
                                $systemPayName = (gs()->site_name ?? 'System') . ' PAY';
                            @endphp
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-wallet"></i> @lang('Payout')</h6>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="d-block border rounded p-3 h-100">
                                        <input type="radio" name="payout_method" value="system" class="me-2 edit-payout-radio"
                                            {{ $payoutMethod === 'system' ? 'checked' : '' }} required>
                                        <strong>{{ __($systemPayName) }}</strong>
                                        <div class="text-muted small mt-1">
                                            @lang('Buyer pays into escrow, and you receive funds in your platform wallet after completion (current behavior).')
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="d-block border rounded p-3 h-100">
                                        <input type="radio" name="payout_method" value="direct" class="me-2 edit-payout-radio"
                                            {{ $payoutMethod === 'direct' ? 'checked' : '' }}>
                                        <strong>@lang('Direct (your own payment link)')</strong>
                                        <div class="text-muted small mt-1">
                                            @lang('Buyer pays you directly outside the platform. A Direct payout listing fee may apply (upfront) if not paid already.')
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4" id="editDirectPayoutFields" style="display: {{ $payoutMethod === 'direct' ? 'block' : 'none' }};">
                                <div class="alert alert-warning border-0">
                                    <strong>@lang('Important'):</strong>
                                    @lang('With Direct payout, the sale amount will NOT be added to your wallet. The buyer will use your link to pay you. A Direct payout listing fee may apply to you (upfront) if not paid already.')
                                </div>
                                <label class="form-label fw-semibold">@lang('Your payment link') <span class="text-danger">*</span></label>
                                <input type="url" name="direct_payment_link" class="form-control"
                                       value="{{ old('direct_payment_link', $listing->direct_payment_link) }}"
                                       placeholder="@lang('https://... (Stripe, PayPal, Paystack, etc.)')">
                                @if(!$listing->direct_payout_fee_paid_at)
                                <div class="mt-3">
                                    <label class="form-label fw-semibold">@lang('Pay Direct payout fee with')</label>
                                    <select name="pay_via" class="form-select form--select">
                                        <option value="1">@lang('Wallet') - {{ showAmount(auth()->user()->balance) }}</option>
                                        <option value="2">@lang('Pay via Gateway')</option>
                                    </select>
                                </div>
                                @endif
                            </div>

                            @push('script')
                                <script>
                                    (function () {
                                        function toggleDirect() {
                                            const checked = document.querySelector('input[name="payout_method"]:checked')?.value || 'system';
                                            const wrap = document.getElementById('editDirectPayoutFields');
                                            if (!wrap) return;
                                            wrap.style.display = checked === 'direct' ? 'block' : 'none';
                                        }
                                        document.addEventListener('change', function (e) {
                                            if (e.target && e.target.classList && e.target.classList.contains('edit-payout-radio')) {
                                                toggleDirect();
                                            }
                                        });
                                        toggleDirect();
                                    })();
                                </script>
                            @endpush
                            
                            <!-- Business-Specific Fields -->
                            @if($listing->business_type == 'domain')
                                <div class="section-header mb-3">
                                    <h6 class="text--base"><i class="las la-globe"></i> @lang('Domain Details')</h6>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">@lang('Domain Name')</label>
                                        <input type="text" name="domain_name" class="form-control" 
                                               value="{{ old('domain_name', $listing->domain_name) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Registrar') <span class="text-danger">*</span></label>
                                        <input type="text" name="domain_registrar" class="form-control" 
                                               value="{{ old('domain_registrar', $listing->domain_registrar) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Expiry Date') <span class="text-danger">*</span></label>
                                        <input type="date" name="domain_expiry" class="form-control" 
                                               min="{{ now()->addDay()->format('Y-m-d') }}"
                                               value="{{ old('domain_expiry', $listing->domain_expiry?->format('Y-m-d')) }}" required>
                                    </div>
                                </div>
                                @php $listingUrl = $listing->slug ? rtrim(config('app.url'), '/') . '/marketplace/listing/' . $listing->slug : null; @endphp
                                <div class="card border-info mb-4">
                                    <div class="card-header bg-info bg-opacity-10 py-2">
                                        <h6 class="mb-0"><i class="las la-link me-1"></i> @lang('general.point_domain_heading')</h6>
                                    </div>
                                    <div class="card-body small">
                                        <p class="text-muted mb-2">@lang('general.redirect_intro')</p>
                                        <p class="text-muted mb-2">@lang('general.redirect_where')</p>
                                        <p class="mb-1">@lang('general.redirect_destination')</p>
                                        @if($listingUrl)
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <code class="dns-value flex-grow-1">{{ $listingUrl }}</code>
                                            <button type="button" class="btn btn-sm btn-outline-secondary dns-copy-btn flex-shrink-0" data-copy="{{ $listingUrl }}">@lang('general.dns_copy_btn')</button>
                                        </div>
                                        @else
                                        <p class="text-muted mb-0">@lang('general.redirect_create_note')</p>
                                        @endif
                                    </div>
                                    <script>
                                    (function() {
                                        document.querySelectorAll('.dns-copy-btn').forEach(function(btn) {
                                            btn.addEventListener('click', function() {
                                                var t = this;
                                                var val = t.getAttribute('data-copy');
                                                if (navigator.clipboard && val) {
                                                    navigator.clipboard.writeText(val).then(function() {
                                                        var orig = t.textContent;
                                                        t.textContent = '{{ __("general.dns_copied") }}';
                                                        setTimeout(function() { t.textContent = orig; }, 1500);
                                                    });
                                                }
                                            });
                                        });
                                    })();
                                    </script>
                                </div>
                            @elseif($listing->business_type == 'website')
                                <div class="section-header mb-3">
                                    <h6 class="text--base"><i class="las la-laptop"></i> @lang('Website Details')</h6>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">@lang('Website URL')</label>
                                        <input type="url" name="website_url" class="form-control" 
                                               value="{{ old('website_url', $listing->url) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Domain Registrar') <span class="text-danger">*</span></label>
                                        <input type="text" name="website_domain_registrar" class="form-control"
                                               value="{{ old('website_domain_registrar', $listing->domain_registrar) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Domain Expiry') <span class="text-danger">*</span></label>
                                        <input type="date" name="website_domain_expiry" class="form-control"
                                               min="{{ now()->addDay()->format('Y-m-d') }}"
                                               value="{{ old('website_domain_expiry', $listing->domain_expiry?->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Niche')</label>
                                        <input type="text" name="niche" class="form-control" 
                                               value="{{ old('niche', $listing->niche) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Tech Stack')</label>
                                        <input type="text" name="tech_stack" class="form-control" 
                                               value="{{ old('tech_stack', $listing->tech_stack) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Business Location')</label>
                                        <input type="text" name="business_location" class="form-control"
                                               value="{{ old('business_location', $listing->business_location) }}"
                                               placeholder="@lang('e.g., United States, Remote, UK')">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Overall Churn (%)')</label>
                                        <input type="number" name="overall_churn_percent" class="form-control"
                                               value="{{ old('overall_churn_percent', $listing->overall_churn_percent) }}"
                                               step="0.01" min="0" max="100" placeholder="0.00">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Site Age (months)')</label>
                                        <input type="number" name="site_age_months" class="form-control"
                                               value="{{ old('site_age_months', $listing->site_age_months) }}"
                                               min="0" placeholder="0">
                                    </div>
                                </div>
                            @elseif($listing->business_type == 'social_media_account')
                                <div class="section-header mb-3">
                                    <h6 class="text--base"><i class="las la-share-alt"></i> @lang('Social Media Details')</h6>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Platform')</label>
                                        <select name="platform" class="form-select">
                                            @foreach($platforms as $key => $name)
                                                <option value="{{ $key }}" @selected($listing->platform == $key)>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Account URL')</label>
                                        <input type="url" name="social_url" class="form-control" value="{{ old('social_url', $listing->url) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Followers Count')</label>
                                        <input type="number" name="followers_count" class="form-control" 
                                               value="{{ old('followers_count', $listing->followers_count) }}" min="0">
                                    </div>
                                </div>
                            @endif

                            @php
                                $monetizationOptions = [
                                    'website' => [
                                        'subscriptions' => 'Subscriptions / Membership',
                                        'ads' => 'Ads (Display)',
                                        'affiliate' => 'Affiliate',
                                        'ecommerce' => 'E-commerce / Products',
                                        'digital_downloads' => 'Digital downloads',
                                        'lead_gen' => 'Lead gen',
                                        'services' => 'Services / Consulting',
                                        'sponsorships' => 'Sponsorships',
                                        'marketplace_commission' => 'Marketplace / Commission',
                                        'saas' => 'SaaS',
                                        'other' => 'Other',
                                    ],
                                    'mobile_app' => [
                                        'subscriptions' => 'Subscriptions',
                                        'one_time_purchase' => 'One-time purchase',
                                        'in_app_purchases' => 'In-app purchases',
                                        'ads' => 'Ads',
                                        'licensing' => 'Licensing / Enterprise',
                                        'support_contracts' => 'Services / Support contracts',
                                        'affiliate' => 'Affiliate',
                                        'other' => 'Other',
                                    ],
                                    'desktop_app' => [
                                        'subscriptions' => 'Subscriptions',
                                        'one_time_purchase' => 'One-time purchase',
                                        'licensing' => 'Licensing / Enterprise',
                                        'ads' => 'Ads',
                                        'support_contracts' => 'Services / Support contracts',
                                        'affiliate' => 'Affiliate',
                                        'other' => 'Other',
                                    ],
                                    'social_media_account' => [
                                        'sponsorships' => 'Brand sponsorships',
                                        'affiliate' => 'Affiliate',
                                        'ad_revenue_share' => 'Ad revenue share',
                                        'sell_products' => 'Selling products',
                                        'sell_services' => 'Selling services',
                                        'donations' => 'Donations / Tips',
                                        'paid_membership' => 'Paid community / Membership',
                                        'other' => 'Other',
                                    ],
                                ];
                                $selectedMonetization = is_array($listing->monetization_methods) ? $listing->monetization_methods : [];
                            @endphp

                            @if(in_array($listing->business_type, ['website','mobile_app','desktop_app','social_media_account']))
                                <div class="section-header mb-3">
                                    <h6 class="text--base"><i class="las la-coins"></i> @lang('Monetization Methods')</h6>
                                </div>
                                <div class="row g-2 mb-3">
                                    @foreach(($monetizationOptions[$listing->business_type] ?? []) as $value => $label)
                                        <div class="col-md-4 col-6">
                                            <label class="form-check">
                                                <input class="form-check-input edit-monetization-method-input"
                                                       type="checkbox"
                                                       name="monetization_methods[]"
                                                       value="{{ $value }}"
                                                       @checked(in_array($value, $selectedMonetization, true))>
                                                <span class="form-check-label">{{ __($label) }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mb-4" id="editMonetizationOtherWrap" style="display: {{ in_array('other', $selectedMonetization, true) ? 'block' : 'none' }};">
                                    <label class="form-label">@lang('Other (describe)')</label>
                                    <input type="text" name="monetization_other" class="form-control"
                                           value="{{ old('monetization_other', $listing->monetization_other) }}"
                                           placeholder="@lang('Describe other monetization method')">
                                </div>

                                @push('script')
                                    <script>
                                        (function () {
                                            function toggleOther() {
                                                const hasOther = document.querySelector('.edit-monetization-method-input[value="other"]')?.checked;
                                                const wrap = document.getElementById('editMonetizationOtherWrap');
                                                if (!wrap) return;
                                                wrap.style.display = hasOther ? 'block' : 'none';
                                            }
                                            document.addEventListener('change', function (e) {
                                                if (e.target && e.target.classList && e.target.classList.contains('edit-monetization-method-input')) {
                                                    toggleOther();
                                                }
                                            });
                                            toggleOther();
                                        })();
                                    </script>
                                @endpush
                            @endif
                            
                            <!-- NDA / Confidentiality -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-file-contract"></i> @lang('NDA & Confidentiality')</h6>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-check d-block border rounded p-3">
                                        <input type="checkbox" name="is_confidential" value="1" class="form-check-input" 
                                               {{ old('is_confidential', $listing->is_confidential) ? 'checked' : '' }}>
                                        <span class="form-check-label fw-semibold">@lang('Confidential listing')</span>
                                        <small class="d-block text-muted mt-1">@lang('Mark this listing as confidential (sensitive details hidden until NDA is signed).')</small>
                                    </label>
                                </div>
                                <div class="col-12">
                                    <label class="form-check d-block border rounded p-3">
                                        <input type="checkbox" name="requires_nda" value="1" class="form-check-input nda-required-toggle" 
                                               {{ old('requires_nda', $listing->requires_nda) ? 'checked' : '' }}>
                                        <span class="form-check-label fw-semibold">@lang('Require NDA to view details')</span>
                                        <small class="d-block text-muted mt-1">@lang('Buyers must sign an NDA before viewing full listing details.')</small>
                                    </label>
                                </div>
                                <div class="col-12" id="confidentialReasonWrap" style="display: {{ old('requires_nda', $listing->requires_nda) ? 'block' : 'none' }};">
                                    <label class="form-label">@lang('Reason for confidentiality') (@lang('optional'))</label>
                                    <textarea name="confidential_reason" class="form-control" rows="2" 
                                              placeholder="@lang('Brief reason for NDA requirement')">{{ old('confidential_reason', $listing->confidential_reason) }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Financials -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-chart-line"></i> @lang('Financials')</h6>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">@lang('MRR (Monthly Revenue)')</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                        <input type="number" name="monthly_revenue" class="form-control" 
                                               value="{{ old('monthly_revenue', $listing->monthly_revenue) }}" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">@lang('Monthly Profit')</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                        <input type="number" name="monthly_profit" class="form-control" 
                                               value="{{ old('monthly_profit', $listing->monthly_profit) }}" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">@lang('Monthly Visitors')</label>
                                    <input type="number" name="monthly_visitors" class="form-control" 
                                           value="{{ old('monthly_visitors', $listing->monthly_visitors) }}" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">@lang('Page Views/Month')</label>
                                    <input type="number" name="monthly_page_views" class="form-control" 
                                           value="{{ old('monthly_page_views', $listing->monthly_page_views) }}" min="0">
                                </div>
                            </div>
                            
                            <!-- Existing Images -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-images"></i> @lang('Current Images')</h6>
                            </div>
                            <div class="row g-3 mb-3" id="listingImagesRow">
                                @foreach($listing->images as $image)
                                    <div class="col-md-3 position-relative image-item" data-id="{{ $image->id }}">
                                        <img src="{{ getImage(getFilePath('listing') . '/' . $image->image) }}" 
                                             class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                                        @if($image->is_primary)
                                            <span class="badge bg-success position-absolute top-0 start-0 m-2">@lang('Primary')</span>
                                        @endif
                                        <div class="mt-2 d-flex gap-2">
                                            @if(!$image->is_primary)
                                                <button type="button" class="btn btn-sm btn-outline-primary set-primary-btn" 
                                                        data-id="{{ $image->id }}">
                                                    @lang('Set Primary')
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-image-btn" 
                                                    data-id="{{ $image->id }}">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Upload image now (saves immediately, no need to click Update) -->
                            <div class="mb-3 p-3 border rounded bg-light">
                                <label class="form-label small fw-semibold mb-2">@lang('Upload image now')</label>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <input type="file" id="uploadImageNowInput" accept="image/jpeg,image/png,image/jpg,image/gif" class="form-control form-control-sm" style="max-width: 220px;">
                                    <span class="text-muted small">@lang('Max 2MB. Saves immediately.')</span>
                                </div>
                                <div id="uploadImageNowProgress" class="mt-2 small text-muted" style="display: none;"></div>
                            </div>
                            
                            <!-- Add More Images (on form submit) -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-images"></i> @lang('Or add more on Update')</h6>
                            </div>
                            <div class="mb-4">
                                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                                <small class="text-muted">@lang('You can also add multiple images when you click Update Listing below.')</small>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('user.listing.index') }}" class="btn btn-secondary">@lang('Cancel')</a>
                                <button type="submit" class="btn btn--base">
                                    <i class="las la-save"></i> @lang('Update Listing')
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    /* Fix form label colors - ensure all labels are visible with black text */
    .form-label {
        color: #000000 !important;
    }
    
    label.form-label {
        color: #000000 !important;
    }
</style>
@endpush

@push('script')
<script>
    $(document).ready(function() {
        // NDA toggle: show/hide confidential reason
        function toggleConfidentialReason() {
            const checked = $('.nda-required-toggle').is(':checked');
            $('#confidentialReasonWrap').toggle(checked);
        }
        $('.nda-required-toggle').on('change', toggleConfidentialReason);
        toggleConfidentialReason();

        // Delete image
        $(document).on('click', '.delete-image-btn', function() {
            if (!confirm('@lang("Are you sure you want to delete this image?")')) return;
            
            const btn = $(this);
            const id = btn.data('id');
            
            $.ajax({
                url: '{{ route("user.listing.image.delete", "") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        btn.closest('.image-item').remove();
                        notify('success', response.message);
                    }
                },
                error: function() {
                    notify('error', 'Failed to delete image');
                }
            });
        });
        
        // Set primary image
        $(document).on('click', '.set-primary-btn', function() {
            const btn = $(this);
            const id = btn.data('id');
            
            $.ajax({
                url: '{{ route("user.listing.image.primary", "") }}/' + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    notify('error', 'Failed to set primary image');
                }
            });
        });

        // Upload image now (saves immediately)
        $('#uploadImageNowInput').on('change', function() {
            const input = this;
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                notify('error', '@lang("Image must be 2MB or less.")');
                input.value = '';
                return;
            }
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('image', file);
            $('#uploadImageNowProgress').show().text('@lang("Uploading...")');
            $.ajax({
                url: '{{ route("user.listing.image.upload", $listing->id) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#uploadImageNowProgress').hide();
                    input.value = '';
                    if (response.success && response.image) {
                        const img = response.image;
                        const primaryBadge = img.is_primary ? '<span class="badge bg-success position-absolute top-0 start-0 m-2">@lang("Primary")</span>' : '';
                        const setPrimaryBtn = img.is_primary ? '' : '<button type="button" class="btn btn-sm btn-outline-primary set-primary-btn" data-id="' + img.id + '">@lang("Set Primary")</button>';
                        const html = '<div class="col-md-3 position-relative image-item" data-id="' + img.id + '">' +
                            '<img src="' + img.url + '" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">' + primaryBadge +
                            '<div class="mt-2 d-flex gap-2">' + setPrimaryBtn +
                            '<button type="button" class="btn btn-sm btn-outline-danger delete-image-btn" data-id="' + img.id + '"><i class="las la-trash"></i></button></div></div>';
                        $('#listingImagesRow').append(html);
                        if (img.is_primary) location.reload();
                        else notify('success', response.message);
                    }
                },
                error: function(xhr) {
                    $('#uploadImageNowProgress').hide();
                    input.value = '';
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.image ? xhr.responseJSON.errors.image[0] : '@lang("Upload failed.")');
                    notify('error', msg);
                }
            });
        });
    });
</script>
@endpush

