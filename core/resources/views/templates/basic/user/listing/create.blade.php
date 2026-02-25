@extends($activeTemplate . 'user.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Progress Steps --}}
            <div class="listing-progress mb-4">
                        <div class="progress-steps" id="progressSteps">
                            <div class="step active" data-step="1"><span class="step-number">1</span><span class="step-text">@lang('Type')</span></div>
                            <div class="step" data-step="2"><span class="step-number">2</span><span class="step-text">@lang('Asset')</span></div>
                            <div class="step" data-step="3"><span class="step-number">3</span><span class="step-text">@lang('Verify')</span></div>
                            <div class="step" data-step="4"><span class="step-number">4</span><span class="step-text">@lang('Details')</span></div>
                            <div class="step" data-step="5"><span class="step-number">5</span><span class="step-text">@lang('Pricing')</span></div>
                            <div class="step" data-step="6"><span class="step-number">6</span><span class="step-text">@lang('Media')</span></div>
                        </div>
                    </div>

            <div class="card b-radius--10">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="las la-plus-circle text--base me-2"></i>
                            @lang('Create New Listing')
                        </h5>
                                @if(!empty($draftData))
                                    <div class="draft-indicator">
                                        <span class="badge bg-info" id="draftStatusBadge">
                                            <i class="las la-save me-1"></i>
                                            @lang('Draft Saved')
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="clearDraftBtn">
                                            <i class="las la-trash"></i> @lang('Clear Draft')
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('user.listing.store') }}" method="POST" enctype="multipart/form-data" id="listingForm" data-is-test-user="{{ $isTestUser ? '1' : '0' }}">
                                @csrf
                                
                                {{-- Hidden fields for validation state --}}
                                <input type="hidden" name="verification_token" id="verificationTokenInput" value="{{ $ownershipValidationData['verification_token'] ?? '' }}">
                                <input type="hidden" name="verification_method" id="verificationMethodInput" value="{{ $ownershipValidationData['verification_method'] ?? '' }}">
                                <input type="hidden" name="is_verified" id="isVerifiedInput" value="{{ ($ownershipValidationData['is_verified'] || $isTestUser) ? '1' : '0' }}">
                                <input type="hidden" name="verification_asset" id="verificationAssetInput" value="{{ $ownershipValidationData['verification_asset'] ?? '' }}">

                                {{-- ============================================
                                     STEP 1: Business Type Selection
                                     ============================================ --}}
                                <div class="form-step" data-step="1">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold mb-1">@lang('What are you selling?')</h5>
                                        <p class="text-muted mb-0">@lang('Select the type of online business you want to list')</p>
                                    </div>
                                    
                                    <div class="business-type-grid mb-4">
                                        @php
                                            $typeIcons = [
                                                'domain' => ['icon' => 'las la-globe', 'color' => '#3b82f6'],
                                                'website' => ['icon' => 'las la-laptop-code', 'color' => '#10b981'],
                                                'social_media_account' => ['icon' => 'las la-users', 'color' => '#8b5cf6'],
                                                'mobile_app' => ['icon' => 'las la-mobile-alt', 'color' => '#f59e0b'],
                                                'desktop_app' => ['icon' => 'las la-desktop', 'color' => '#ef4444'],
                                            ];
                                        @endphp
                                        
                                        @foreach($businessTypes as $key => $name)
                                            @if(($marketplaceSettings['allow_' . $key] ?? '1') == '1')
                                                <label class="business-type-card">
                                                    <input type="radio" name="business_type" value="{{ $key }}" 
                                                            {{ old('business_type', $draftData['business_type'] ?? '') == $key ? 'checked' : '' }} required>
                                                    <div class="card-inner">
                                                        <div class="type-icon" style="background: {{ $typeIcons[$key]['color'] ?? '#6b7280' }}20; color: {{ $typeIcons[$key]['color'] ?? '#6b7280' }}">
                                                            <i class="{{ $typeIcons[$key]['icon'] ?? 'las la-box' }}"></i>
                                                        </div>
                                                        <h6 class="type-name">{{ $name }}</h6>
                                                        <span class="check-mark"><i class="las la-check"></i></span>
                                                    </div>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                    
                                    <div class="alert alert-info mt-4">
                                        <i class="las la-info-circle me-2"></i>
                                        <strong>@lang('Next Step:')</strong> 
                                        @lang('After selecting a business type, you will be asked to enter the specific asset details.')
                                    </div>
                                    
                                    <div class="step-actions mt-4 d-flex justify-content-end">
                                        <button type="button" class="btn btn--base btn-next fw-bold" data-step="2" id="step1ContinueBtn">
                                            @lang('Continue') <i class="las la-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- ============================================
                                     STEP 2: Asset Entry (Domain/Website/Social Media/App)
                                     ============================================ --}}
                                <div class="form-step d-none" data-step="2">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold mb-1">@lang('Enter Asset Details')</h5>
                                        <p class="text-muted mb-0">@lang('Provide information about the specific asset you are selling')</p>
                                    </div>
                                    
                                    {{-- Domain Input Section --}}
                                    <div id="domainInputSection" class="business-fields domain-fields mb-4" style="display: none;">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary bg-opacity-10">
                                                <h6 class="mb-0">
                                                    <i class="las la-globe me-2"></i>@lang('Domain Information')
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">@lang('Domain Name') <span class="text-danger">*</span></label>
                                                    <div class="input-group input-group-lg">
                                                        <span class="input-group-text bg-light"><i class="las la-globe"></i></span>
                                                        <input type="url" name="domain_name" id="domainNameInput" class="form-control" 
                                                                value="{{ old('domain_name', $draftData['domain_name'] ?? '') }}" placeholder="https://example.com" data-required="domain">
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="las la-info-circle"></i> 
                                                        <span id="domainHelpText">@lang('Enter domain with http:// or https:// (e.g., https://example.com)')</span>
                                                    </small>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Domain Registrar') <span class="text-danger">*</span></label>
                                                        <input type="text" name="domain_registrar" id="domainRegistrarInput" class="form-control"
                                                                value="{{ old('domain_registrar', $draftData['domain_registrar'] ?? '') }}" placeholder="@lang('e.g., GoDaddy, Namecheap')">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Expiry Date') <span class="text-danger">*</span></label>
                                                        <input type="date" name="domain_expiry" id="domainExpiryInput" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ old('domain_expiry', $draftData['domain_expiry'] ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Domain age (years)')</label>
                                                        <input type="number" name="domain_age_years" class="form-control" min="0" step="0.5"
                                                                value="{{ old('domain_age_years', $draftData['domain_age_years'] ?? '') }}" placeholder="0">
                                                        <small class="text-muted">@lang('Optional. Years you have owned the domain.')</small>
                                                    </div>
                                                </div>
                                                <div class="mt-4 pt-3 border-top" id="domainRedirectInstructions">
                                                    <p class="text-muted small mb-2"><em>@lang('general.dns_optional_note')</em></p>
                                                    <h6 class="mb-2"><i class="las la-link me-1"></i> @lang('general.point_domain_heading')</h6>
                                                    <p class="text-muted small mb-2">@lang('general.redirect_intro')</p>
                                                    <p class="text-muted small mb-2">@lang('general.redirect_where')</p>
                                                    <p class="text-muted small mb-0">@lang('general.redirect_create_note')</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Website Input Section --}}
                                    <div id="websiteInputSection" class="business-fields website-fields mb-4" style="display: none;">
                                        <div class="card border-success">
                                            <div class="card-header bg-success bg-opacity-10">
                                                <h6 class="mb-0">
                                                    <i class="las la-laptop-code me-2"></i>@lang('Website Information')
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">@lang('Website URL') <span class="text-danger">*</span></label>
                                                    <div class="input-group input-group-lg">
                                                        <span class="input-group-text bg-light"><i class="las la-link"></i></span>
                                                        <input type="url" name="website_url" id="websiteUrlInput" class="form-control" 
                                                                value="{{ old('website_url', $draftData['website_url'] ?? '') }}" placeholder="https://example.com" data-required="website">
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="las la-info-circle"></i> 
                                                        <span id="websiteHelpText">@lang('Enter full URL starting with http:// or https://')</span>
                                                    </small>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Website Niche')</label>
                                                        <input type="text" name="website_niche" class="form-control" 
                                                                value="{{ old('website_niche', $draftData['website_niche'] ?? '') }}" placeholder="@lang('e.g., Technology, Health, Finance')">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Tech Stack / Platform')</label>
                                                        <input type="text" name="website_tech_stack" class="form-control" 
                                                                value="{{ old('website_tech_stack', $draftData['website_tech_stack'] ?? '') }}" placeholder="@lang('e.g., WordPress, Shopify, Laravel')">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Domain Registrar') <span class="text-danger">*</span></label>
                                                        <input type="text" name="website_domain_registrar" id="websiteRegistrarInput" class="form-control" 
                                                                value="{{ old('website_domain_registrar', $draftData['website_domain_registrar'] ?? '') }}" placeholder="@lang('e.g., GoDaddy, Namecheap')">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Domain Expiry') <span class="text-danger">*</span></label>
                                                        <input type="date" name="website_domain_expiry" id="websiteExpiryInput" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ old('website_domain_expiry', $draftData['website_domain_expiry'] ?? '') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Social Media Fields --}}
                                    <div class="business-fields social_media_account-fields mb-4" style="display: none;">
                                        <div class="card border-purple">
                                            <div class="card-header bg-purple bg-opacity-10">
                                                <h6 class="mb-0">
                                                    <i class="las la-users me-2"></i>@lang('Social Media Account Information')
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">@lang('Platform') <span class="text-danger">*</span></label>
                                                        <select name="platform" id="socialPlatformSelect" class="form-select form-select-lg" data-required="social_media_account">
                                                            <option value="">@lang('Select Platform')</option>
                                                            @foreach($platforms as $key => $name)
                                                                <option value="{{ $key }}" {{ old('platform', $draftData['platform'] ?? '') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    
                                                    {{-- OAuth Login Section (for platforms that support it) --}}
                                                    <div class="col-12" id="oauthLoginSection" style="display: none;">
                                                        <div class="alert alert-info mb-3">
                                                            <i class="las la-info-circle me-2"></i>
                                                            <strong>@lang('Quick Connect')</strong>
                                                            <p class="mb-0 mt-2">@lang('Connect your account securely via OAuth. We\'ll automatically fetch your username, followers, and engagement rate. No passwords are stored.')</p>
                                                        </div>
                                                        <div id="oauthLoginButtonContainer"></div>
                                                        <hr class="my-3">
                                                        <p class="text-muted text-center mb-0">
                                                            <small>@lang('Or') <a href="#" id="showManualInputLink" class="text-decoration-none">@lang('enter details manually')</a></small>
                                                        </p>
                                                    </div>
                                                    
                                                    {{-- Manual Input Section --}}
                                                    <div class="col-12" id="manualInputSection">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">@lang('Username / Handle') <span class="text-danger" id="usernameRequired">*</span></label>
                                                                <div class="input-group input-group-lg">
                                                                    <span class="input-group-text bg-light">@</span>
                                                                    <input type="text" name="social_username" id="socialUsernameInput" class="form-control" 
                                                                            value="{{ old('social_username', $draftData['social_username'] ?? '') }}" placeholder="username" data-required="social_media_account">
                                                                </div>
                                                                <small class="text-muted" id="oauthConnectedBadge" style="display: none;">
                                                                    <i class="las la-check-circle text-success"></i> @lang('Connected via OAuth')
                                                                </small>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">@lang('Account URL')</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light"><i class="las la-link"></i></span>
                                                                    <input type="url" name="social_url" id="socialUrlInput" class="form-control" 
                                                                            value="{{ old('social_url', $draftData['social_url'] ?? '') }}" placeholder="https://instagram.com/username">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">@lang('Followers')</label>
                                                                <input type="number" name="followers_count" id="followersCountInput" class="form-control" 
                                                                        value="{{ old('followers_count', $draftData['followers_count'] ?? '') }}" placeholder="0" min="0">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">@lang('Engagement Rate') (%)</label>
                                                                <input type="number" name="engagement_rate" id="engagementRateInput" class="form-control" 
                                                                        value="{{ old('engagement_rate', $draftData['engagement_rate'] ?? '') }}" step="0.01" min="0" max="100" placeholder="0.00">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">@lang('Account Niche')</label>
                                                                <input type="text" name="social_niche" class="form-control" 
                                                                        value="{{ old('social_niche', $draftData['social_niche'] ?? '') }}" placeholder="@lang('e.g., Fashion, Gaming')">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Hidden field to track if OAuth was used --}}
                                                    <input type="hidden" name="oauth_connected" id="oauthConnectedInput" value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Mobile App Fields --}}
                                    <div class="business-fields mobile_app-fields mb-4" style="display: none;">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning bg-opacity-10">
                                                <h6 class="mb-0">
                                                    <i class="las la-mobile-alt me-2"></i>@lang('Mobile App Information')
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">@lang('App Name') <span class="text-danger">*</span></label>
                                                        <input type="text" name="mobile_app_name" class="form-control form-control-lg" 
                                                                value="{{ old('mobile_app_name', $draftData['mobile_app_name'] ?? '') }}" placeholder="@lang('Your App Name')" data-required="mobile_app">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Tech Stack')</label>
                                                        <input type="text" name="mobile_tech_stack" class="form-control form-control-lg" 
                                                                value="{{ old('mobile_tech_stack', $draftData['mobile_tech_stack'] ?? '') }}" placeholder="@lang('e.g., React Native, Flutter')">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">@lang('App Store URL (iOS)')</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light"><i class="lab la-apple"></i></span>
                                                            <input type="url" name="app_store_url" class="form-control" 
                                                                    value="{{ old('app_store_url', $draftData['app_store_url'] ?? '') }}" placeholder="https://apps.apple.com/...">
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">@lang('Play Store URL (Android)')</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light"><i class="lab la-google-play"></i></span>
                                                            <input type="url" name="play_store_url" class="form-control" 
                                                                    value="{{ old('play_store_url', $draftData['play_store_url'] ?? '') }}" placeholder="https://play.google.com/...">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">@lang('Total Downloads')</label>
                                                        <input type="number" name="downloads_count" class="form-control" 
                                                                value="{{ old('downloads_count', $draftData['downloads_count'] ?? '') }}" placeholder="0" min="0">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">@lang('Rating') (0-5)</label>
                                                        <input type="number" name="app_rating" class="form-control" 
                                                                value="{{ old('app_rating', $draftData['app_rating'] ?? '') }}" step="0.1" min="0" max="5" placeholder="4.5">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">@lang('Active Users')</label>
                                                        <input type="number" name="active_users" class="form-control" 
                                                                value="{{ old('active_users', $draftData['active_users'] ?? '') }}" placeholder="0" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Desktop App Fields --}}
                                    <div class="business-fields desktop_app-fields mb-4" style="display: none;">
                                        <div class="card border-danger">
                                            <div class="card-header bg-danger bg-opacity-10">
                                                <h6 class="mb-0">
                                                    <i class="las la-desktop me-2"></i>@lang('Desktop App Information')
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">@lang('App Name') <span class="text-danger">*</span></label>
                                                        <input type="text" name="desktop_app_name" class="form-control form-control-lg" 
                                                                value="{{ old('desktop_app_name', $draftData['desktop_app_name'] ?? '') }}" placeholder="@lang('Your App Name')" data-required="desktop_app">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('Tech Stack')</label>
                                                        <input type="text" name="desktop_tech_stack" class="form-control form-control-lg" 
                                                                value="{{ old('desktop_tech_stack', $draftData['desktop_tech_stack'] ?? '') }}" placeholder="@lang('e.g., Electron, .NET, Java')">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">@lang('Download / Website URL')</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light"><i class="las la-download"></i></span>
                                                            <input type="url" name="desktop_url" class="form-control" 
                                                                    value="{{ old('desktop_url', $draftData['desktop_url'] ?? '') }}" placeholder="https://yourapp.com/download">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">@lang('Total Downloads')</label>
                                                        <input type="number" name="desktop_downloads_count" class="form-control" 
                                                                value="{{ old('desktop_downloads_count', $draftData['desktop_downloads_count'] ?? '') }}" placeholder="0" min="0">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">@lang('Active Users')</label>
                                                        <input type="number" name="desktop_active_users" class="form-control" 
                                                                value="{{ old('desktop_active_users', $draftData['desktop_active_users'] ?? '') }}" placeholder="0" min="0">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">@lang('Supported Platforms')</label>
                                                        <input type="text" name="supported_platforms" class="form-control" 
                                                                value="{{ old('supported_platforms', $draftData['supported_platforms'] ?? '') }}" placeholder="@lang('Windows, Mac, Linux')">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-actions mt-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline--secondary btn-prev" data-step="1">
                                            <i class="las la-arrow-left me-2"></i> @lang('Back')
                                        </button>
                                        <button type="button" class="btn btn--base btn-next fw-bold" data-step="3" id="step2ContinueBtn">
                                            @lang('Continue') <i class="las la-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- ============================================
                                     STEP 3: Ownership Verification (Conditional)
                                     ============================================ --}}
                                <div class="form-step d-none" data-step="3" id="ownershipVerificationStep">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold mb-1">@lang('Verify Ownership')</h5>
                                        <p class="text-muted mb-0">@lang('Verify that you own this asset before continuing')</p>
                                    </div>
                                    
                                    @if($isTestUser)
                                    {{-- Test users skip ownership verification --}}
                                    <div class="alert alert-info mb-4">
                                        <i class="las la-info-circle me-2"></i>
                                        <strong>@lang('Ownership verification not required')</strong>
                                        <p class="mb-0 mt-2">@lang('Ownership verification is not required for test accounts. You can proceed to the next step.')</p>
                                    </div>
                                    @else
                                    {{-- Ownership Validation Section --}}
                                    <div id="ownershipValidationSection" class="mb-4" style="display: none;">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning bg-opacity-10">
                                                <h6 class="mb-0">
                                                    <i class="las la-shield-alt me-2"></i>@lang('Ownership Validation') <span class="text-danger">*</span>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-3">
                                                    @lang('To ensure you own this asset, please verify ownership using one of the methods below.')
                                                </p>
                                                
                                                <div id="validationMethodsContainer" class="mb-3">
                                                    <label class="form-label fw-semibold">@lang('Select Verification Method')</label>
                                                    <div id="validationMethodsList">
                                                        <div class="text-center py-2"><i class="las la-spinner la-spin"></i> <small>Loading methods...</small></div>
                                                    </div>
                                                </div>
                                                
                                                <div id="validationInstructions" class="alert alert-info" style="display: none;">
                                                    <h6 class="alert-heading">@lang('Instructions')</h6>
                                                    <div id="instructionsContent"></div>
                                                </div>
                                                
                                                <div id="validationResult" class="mt-3" style="display: none;"></div>
                                                
                                                <div id="oauthLoginButtons" class="mt-3" style="display: none;"></div>

                                                <div class="d-flex gap-2 mt-3" id="verificationActionButtons">
                                                    <button type="button" class="btn btn--base" id="generateTokenBtn" style="display: none;">
                                                        <i class="las la-key me-1"></i>@lang('Generate Verification Token')
                                                    </button>
                                                    <button type="button" class="btn btn-success" id="validateOwnershipBtn" style="display: none;">
                                                        <i class="las la-check-circle me-1"></i>@lang('Validate Ownership')
                                                    </button>
                                                </div>
                                                
                                                <div id="validationStatus" class="mt-3" style="display: none;">
                                                    <div class="alert alert-success">
                                                        <i class="las la-check-circle me-2"></i>
                                                        <strong>@lang('Ownership Verified!')</strong>
                                                        <p class="mb-0 mt-1">@lang('You can now continue with your listing.')</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Skip message for types that don't require verification --}}
                                    <div id="verificationNotRequiredMessage" class="alert alert-info" style="display: none;">
                                        <i class="las la-info-circle me-2"></i>
                                        <strong>@lang('Ownership verification not required')</strong>
                                        <p class="mb-0 mt-2">@lang('Ownership verification is not required for this business type. You can proceed to the next step.')</p>
                                    </div>
                                    @endif
                                    
                                    <div class="step-actions mt-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline--secondary btn-prev" data-step="2">
                                            <i class="las la-arrow-left me-2"></i> @lang('Back')
                                        </button>
                                        <button type="button" class="btn btn--base btn-next fw-bold" data-step="4" id="step3ContinueBtn">
                                            @lang('Continue') <i class="las la-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- ============================================
                                     STEP 4: Business Details 
                                     ============================================ --}}
                                <div class="form-step d-none" data-step="4">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold mb-1">@lang('Business Details')</h5>
                                        <p class="text-muted mb-0">@lang('Provide information about your business')</p>
                                    </div>
                                    
                                    {{-- Common Fields for All Types --}}
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">@lang('Category') <span class="text-danger">*</span></label>
                                            <select name="listing_category_id" class="form-select form-select-lg" id="listingCategory" required>
                                                <option value="">@lang('Select Category')</option>
                                                @foreach($listingCategories as $category)
                                                    <option value="{{ $category->id }}" data-type="{{ $category->business_type }}" 
                                                             {{ old('listing_category_id', $draftData['listing_category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">@lang('Description') <span class="text-danger">*</span></label>
                                            <textarea name="description" class="form-control" rows="6" required
                                                     data-min-length="{{ $marketplaceSettings['min_listing_description'] ?? 100 }}"
                                                     data-placeholder-domain="@lang('Describe the domain, its potential use cases, why you\'re selling, and any notable history or backlinks.')"
                                                     data-placeholder-other="@lang('Describe your business, traffic sources, monetization, growth potential, and what is included in the sale.')"
                                                     placeholder="@lang('Describe your business, traffic sources, monetization, growth potential, and what is included in the sale.')">{{ old('description', $draftData['description'] ?? '') }}</textarea>
                                            <small class="text-muted">@lang('Minimum') {{ $marketplaceSettings['min_listing_description'] ?? 100 }} @lang('characters. Be detailed to attract serious buyers.')</small>
                                        </div>
                                    </div>
                                    
                                    {{-- Financials Section (Conditional based on type) --}}
                                    <div class="mt-4 p-3 bg-light rounded financial-section">
                                        <h6 class="fw-bold mb-3"><i class="las la-chart-line me-2"></i>@lang('Financial Information') <small class="text-muted financial-note">(@lang('Optional, except for certain business types'))</small></h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">@lang('MRR (Monthly Revenue)')</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                                    <input type="number" name="monthly_revenue" class="form-control" 
                                                            value="{{ old('monthly_revenue', $draftData['monthly_revenue'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">@lang('Monthly Profit')</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                                    <input type="number" name="monthly_profit" class="form-control" 
                                                            value="{{ old('monthly_profit', $draftData['monthly_profit'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">@lang('Monthly Visitors')</label>
                                                <input type="number" name="monthly_visitors" class="form-control" 
                                                        value="{{ old('monthly_visitors', $draftData['monthly_visitors'] ?? '') }}" min="0" placeholder="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">@lang('Page Views/Month')</label>
                                                <input type="number" name="monthly_page_views" class="form-control" 
                                                        value="{{ old('monthly_page_views', $draftData['monthly_page_views'] ?? '') }}" min="0" placeholder="0">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Website business metrics --}}
                                    <div class="mt-4 p-3 border rounded website-business-metrics" style="display:none;">
                                        <h6 class="fw-bold mb-3"><i class="las la-chart-pie me-2"></i>@lang('Website Metrics')</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('Business Location')</label>
                                                <input type="text" name="business_location" class="form-control"
                                                       value="{{ old('business_location', $draftData['business_location'] ?? '') }}"
                                                       placeholder="@lang('e.g., United States, Remote, UK')">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('Overall Churn (%)')</label>
                                                <input type="number" name="overall_churn_percent" class="form-control"
                                                       value="{{ old('overall_churn_percent', $draftData['overall_churn_percent'] ?? '') }}"
                                                       step="0.01" min="0" max="100" placeholder="0.00">
                                                <small class="text-muted">@lang('Enter a percentage from 0 to 100')</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('Site Age (months)')</label>
                                                <input type="number" name="site_age_months" class="form-control"
                                                       value="{{ old('site_age_months', $draftData['site_age_months'] ?? '') }}"
                                                       min="0" placeholder="0">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Monetization methods (Website / Apps / Social) --}}
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
                                        $selectedMonetization = (array) old('monetization_methods', $draftData['monetization_methods'] ?? []);
                                    @endphp
                                    <div class="mt-4 p-3 border rounded monetization-section" style="display:none;">
                                        <h6 class="fw-bold mb-3"><i class="las la-coins me-2"></i>@lang('Monetization Methods')</h6>
                                        <p class="text-muted small mb-3">@lang('Select all that apply.')</p>

                                        @foreach($monetizationOptions as $typeKey => $options)
                                            <div class="monetization-options monetization-{{ $typeKey }}" style="display:none;">
                                                <div class="row g-2">
                                                    @foreach($options as $value => $label)
                                                        <div class="col-md-4 col-6">
                                                            <label class="form-check">
                                                                <input class="form-check-input monetization-method-input"
                                                                       type="checkbox"
                                                                       name="monetization_methods[]"
                                                                       value="{{ $value }}"
                                                                       @checked(in_array($value, $selectedMonetization, true))>
                                                                <span class="form-check-label">{{ __($label) }}</span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="mt-3 monetization-other-wrap" style="display:none;">
                                            <label class="form-label">@lang('Other (describe)')</label>
                                            <input type="text" name="monetization_other" class="form-control"
                                                   value="{{ old('monetization_other', $draftData['monetization_other'] ?? '') }}"
                                                   placeholder="@lang('Describe other monetization method')">
                                        </div>
                                    </div>
                                    
                                    {{-- Confidential & NDA Section --}}
                                    <div class="mt-4 p-3 border rounded">
                                        <h6 class="fw-bold mb-3"><i class="las la-shield-alt me-2"></i>@lang('Confidentiality & NDA Settings')</h6>
                                        <p class="text-muted small mb-3">@lang('Protect sensitive information by making your listing confidential and requiring an NDA before buyers can view details.')</p>
                                        
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_confidential" id="isConfidential" 
                                                            value="1" {{ old('is_confidential', $draftData['is_confidential'] ?? '') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold" for="isConfidential">
                                                        @lang('Make this listing confidential')
                                                    </label>
                                                    <small class="text-muted d-block mt-1">
                                                        @lang('Confidential listings hide sensitive details from unauthorized users.')
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12" id="ndaSection" style="display: {{ old('is_confidential', $draftData['is_confidential'] ?? '') ? 'block' : 'none' }};">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="requires_nda" id="requiresNda" 
                                                            value="1" {{ old('requires_nda', $draftData['requires_nda'] ?? '') ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold" for="requiresNda">
                                                        @lang('Require NDA before viewing details')
                                                    </label>
                                                    <small class="text-muted d-block mt-1">
                                                        @lang('Buyers must sign a Non-Disclosure Agreement before they can view confidential details of your listing.')
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12" id="confidentialReasonSection" style="display: {{ old('is_confidential', $draftData['is_confidential'] ?? '') ? 'block' : 'none' }};">
                                                <label class="form-label fw-semibold">@lang('Reason for Confidentiality')</label>
                                                <textarea name="confidential_reason" class="form-control" rows="3" 
                                                          placeholder="@lang('Explain why this listing is confidential (e.g., sensitive financial data, proprietary technology, etc.)')">{{ old('confidential_reason', $draftData['confidential_reason'] ?? '') }}</textarea>
                                                <small class="text-muted">@lang('This information helps buyers understand why an NDA is required.')</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-actions mt-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline--secondary btn-prev" data-step="3">
                                            <i class="las la-arrow-left me-2"></i> @lang('Back')
                                        </button>
                                        <button type="button" class="btn btn--base btn-next fw-bold" data-step="5">
                                            @lang('Continue') <i class="las la-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- ============================================
                                     STEP 5: Sale Type & Pricing
                                     ============================================ --}}
                                <div class="form-step d-none" data-step="5">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold mb-1">@lang('How do you want to sell?')</h5>
                                        <p class="text-muted mb-0">@lang('Choose your sale method and set your price')</p>
                                    </div>
                                    
                                    {{-- Sale Type Selection --}}
                                    <div class="sale-type-grid mb-4">
                                        @if(($marketplaceSettings['allow_fixed_price'] ?? '1') == '1')
                                            <label class="sale-type-card">
                                                <input type="radio" name="sale_type" value="fixed_price" id="fixedPriceRadio" 
                                                        {{ old('sale_type', $draftData['sale_type'] ?? 'fixed_price') == 'fixed_price' ? 'checked' : '' }} required>
                                                <div class="card-inner">
                                                    <div class="sale-icon">
                                                        <i class="las la-tag"></i>
                                                    </div>
                                                    <div class="sale-info">
                                                        <h6 class="mb-1">@lang('Fixed Price')</h6>
                                                        <p class="small text-muted mb-0">@lang('Set a specific price for your business')</p>
                                                    </div>
                                                    <span class="check-mark"><i class="las la-check"></i></span>
                                                </div>
                                            </label>
                                        @endif
                                        
                                        @if(($marketplaceSettings['allow_auctions'] ?? '1') == '1')
                                            <label class="sale-type-card">
                                                <input type="radio" name="sale_type" value="auction" id="auctionRadio" 
                                                        {{ old('sale_type', $draftData['sale_type'] ?? '') == 'auction' ? 'checked' : '' }}>
                                                <div class="card-inner">
                                                    <div class="sale-icon auction">
                                                        <i class="las la-gavel"></i>
                                                    </div>
                                                    <div class="sale-info">
                                                        <h6 class="mb-1">@lang('Auction')</h6>
                                                        <p class="small text-muted mb-0">@lang('Let buyers bid for your business')</p>
                                                    </div>
                                                    <span class="check-mark"><i class="las la-check"></i></span>
                                                </div>
                                            </label>
                                        @endif
                                    </div>
                                    
                                    {{-- Fixed Price Fields --}}
                                    <div class="pricing-fields fixed-price-fields" style="display: {{ old('sale_type', $draftData['sale_type'] ?? 'fixed_price') == 'fixed_price' ? 'block' : 'none' }}">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">@lang('Asking Price') <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                    <input type="number" name="asking_price" id="askingPriceInput" class="form-control" 
                                                            value="{{ old('asking_price', $draftData['asking_price'] ?? '') }}" step="0.01" min="1" placeholder="0.00">
                                                </div>
                                                <small class="text-muted">@lang('The price you want to sell for')</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">@lang('Allow Offers?')</label>
                                                <select name="allow_offers" class="form-select form-select-lg">
                                                    <option value="1" {{ old('allow_offers', $draftData['allow_offers'] ?? '1') == '1' ? 'selected' : '' }}>@lang('Yes, accept offers from buyers')</option>
                                                    <option value="0" {{ old('allow_offers', $draftData['allow_offers'] ?? '') == '0' ? 'selected' : '' }}>@lang('No, fixed price only')</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Auction Fields --}}
                                    <div class="pricing-fields auction-fields" style="display: {{ old('sale_type', $draftData['sale_type'] ?? '') == 'auction' ? 'block' : 'none' }}">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">@lang('Starting Bid') <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                    <input type="number" name="starting_bid" id="startingBidInput" class="form-control" 
                                                            value="{{ old('starting_bid', $draftData['starting_bid'] ?? '') }}" step="0.01" min="1" placeholder="0.00">
                                                </div>
                                                <small class="text-muted">@lang('Minimum bid to start the auction')</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">@lang('Reserve Price') <small class="text-muted">(@lang('Optional'))</small></label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                    <input type="number" name="reserve_price" id="reservePriceInput" class="form-control" 
                                                            value="{{ old('reserve_price', $draftData['reserve_price'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                                </div>
                                                <small class="text-muted">@lang('Minimum price you will accept (hidden from buyers)')</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('Buy Now Price') <small class="text-muted">(@lang('Optional'))</small></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                    <input type="number" name="buy_now_price" class="form-control" 
                                                            value="{{ old('buy_now_price', $draftData['buy_now_price'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('Bid Increment') <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                    <input type="number" name="bid_increment" id="bidIncrementInput" class="form-control" 
                                                            value="{{ old('bid_increment', $draftData['bid_increment'] ?? 10) }}" step="0.01" min="1" placeholder="10.00">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('Auction Duration') <span class="text-danger">*</span></label>
                                                @php
                                                    $minDays = $marketplaceSettings['min_auction_days'] ?? 1;
                                                    $maxDays = $marketplaceSettings['max_auction_days'] ?? 30;
                                                    // Generate options dynamically based on min/max
                                                    $durationOptions = [];
                                                    // Common durations: 1, 3, 5, 7, 10, 14, 21, 30 days, then weekly up to max
                                                    $commonDurations = [1, 3, 5, 7, 10, 14, 21, 30];
                                                    foreach ($commonDurations as $days) {
                                                        if ($days >= $minDays && $days <= $maxDays) {
                                                            $durationOptions[] = $days;
                                                        }
                                                    }
                                                    // Add weekly increments if max is greater than 30
                                                    if ($maxDays > 30) {
                                                        for ($days = 35; $days <= $maxDays; $days += 7) {
                                                            if ($days >= $minDays && $days <= $maxDays) {
                                                                $durationOptions[] = $days;
                                                            }
                                                        }
                                                    }
                                                    // Ensure min and max are included
                                                    if (!in_array($minDays, $durationOptions) && $minDays > 0) {
                                                        array_unshift($durationOptions, $minDays);
                                                    }
                                                    if (!in_array($maxDays, $durationOptions) && $maxDays > 0) {
                                                        $durationOptions[] = $maxDays;
                                                    }
                                                    $durationOptions = array_unique($durationOptions);
                                                    sort($durationOptions);
                                                    $defaultDuration = max($minDays, min(7, $maxDays)); // Default to 7 days or closest valid value
                                                @endphp
                                                <select name="auction_duration" id="auctionDurationSelect" class="form-select" required>
                                                    @foreach($durationOptions as $days)
                                                        <option value="{{ $days }}" {{ old('auction_duration', $draftData['auction_duration'] ?? $defaultDuration) == $days ? 'selected' : '' }}>{{ $days }} @lang('days')</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">@lang('Duration must be between :min and :max days', ['min' => $minDays, 'max' => $maxDays])</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Payout method --}}
                                    @php
                                        $payoutMethod = old('payout_method', $draftData['payout_method'] ?? 'system');
                                        $systemPayName = (gs()->site_name ?? 'System') . ' PAY';
                                    @endphp
                                    <div class="mt-4 p-3 border rounded">
                                        <h6 class="fw-bold mb-2"><i class="las la-wallet me-2"></i>@lang('How do you want to receive payment?')</h6>
                                        <p class="text-muted small mb-3">
                                            @lang('If you choose Direct, the buyer pays you outside the platform using your payment link. We still open escrow for communication and to track the sale, but we do not hold the sale amount in your wallet. The buyer is not charged an additional platform fee for Direct payout; instead, a Direct payout listing fee may apply to you (upfront).')
                                        </p>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="payout-card d-block border rounded p-3 h-100">
                                                    <input type="radio" name="payout_method" value="system" class="me-2 payout-method-radio"
                                                        {{ $payoutMethod === 'system' ? 'checked' : '' }} required>
                                                    <strong>{{ __($systemPayName) }}</strong>
                                                    <div class="text-muted small mt-1">
                                                        @lang('Buyer pays into escrow, and you receive funds in your platform wallet after completion.')
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="payout-card d-block border rounded p-3 h-100">
                                                    <input type="radio" name="payout_method" value="direct" class="me-2 payout-method-radio"
                                                        {{ $payoutMethod === 'direct' ? 'checked' : '' }}>
                                                    <strong>@lang('Direct (your own payment link)')</strong>
                                                    <div class="text-muted small mt-1">
                                                        @lang('Buyer pays you directly. You will pay a Direct payout listing fee upfront before you can submit this listing.')
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mt-3 direct-payout-fields" style="display: {{ $payoutMethod === 'direct' ? 'block' : 'none' }};">
                                            <div class="alert alert-warning border-0">
                                                <strong>@lang('Important'):</strong>
                                                @lang('With Direct payout, the sale amount will NOT be added to your wallet. The buyer will use your link to pay you. A Direct payout listing fee may apply to you (upfront).')
                                            </div>

                                            <div class="direct-fee-card" id="directPayoutFeePreview">
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="direct-fee-icon">
                                                        <i class="las la-receipt"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="direct-fee-title">@lang('Direct payout listing fee')</div>
                                                            <span class="direct-fee-badge">@lang('Upfront')</span>
                                                        </div>
                                                        <div class="direct-fee-main mt-1" id="directPayoutFeePreviewText">
                                                            @lang('Calculating...')
                                                        </div>
                                                        <div class="direct-fee-sub mt-2">
                                                            @lang('Pay this fee from your wallet or pay via gateway when you submit the listing (Step 6).')
                                                            <a href="{{ route('user.deposit.index') }}" class="ms-1">@lang('Deposit funds')</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">@lang('Pay Direct payout fee with')</label>
                                                <select name="pay_via" class="form-select form--select">
                                                    <option value="1">@lang('Wallet') - {{ showAmount(auth()->user()->balance) }}</option>
                                                    <option value="2">@lang('Pay via Gateway')</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">@lang('Your payment link') <span class="text-danger">*</span></label>
                                                <input type="url" name="direct_payment_link" id="directPaymentLinkInput" class="form-control"
                                                       value="{{ old('direct_payment_link', $draftData['direct_payment_link'] ?? '') }}"
                                                       placeholder="@lang('https://... (Stripe, PayPal, Paystack, etc.)')">
                                                <small class="text-muted">@lang('This link will be shown to the buyer inside escrow for payment outside the platform.')</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-actions mt-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline--secondary btn-prev" data-step="4">
                                            <i class="las la-arrow-left me-2"></i> @lang('Back')
                                        </button>
                                        <button type="button" class="btn btn--base btn-next fw-bold" data-step="6">
                                            @lang('Continue') <i class="las la-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- ============================================
                                     STEP 6: Media & Review
                                     ============================================ --}}
                                <div class="form-step d-none" data-step="6">
                                    <div class="step-header mb-4">
                                        <h5 class="fw-bold mb-1">@lang('Media & Review')</h5>
                                        <p class="text-muted mb-0">@lang('Upload images/screenshots and review your listing')</p>
                                    </div>
                                    
                                    {{-- Domain Information Message (conditional display) --}}
                                    <div class="domain-info-message d-none mb-4">
                                        @php
                                            $requiresApproval = ($marketplaceSettings['listing_approval_required'] ?? '1') == '1';
                                        @endphp
                                        <div class="alert alert-info border-0 shadow-sm">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0 me-3">
                                                    <i class="las la-info-circle" style="font-size: 2rem; color: #0dcaf0;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="alert-heading mb-2">
                                                        <i class="las la-globe me-2"></i>@lang('Domain Listing')
                                                    </h5>
                                                    @if($requiresApproval)
                                                        <p class="mb-2">@lang('Your domain listing will be reviewed and approved before going live.')</p>
                                                        <p class="mb-0 small text-muted"><i class="las la-clock me-1"></i>@lang('Approval usually takes less than a day.')</p>
                                                    @else
                                                        <p class="mb-0">@lang('Your domain listing will be published immediately after submission.')</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Image Upload (conditional display) --}}
                                    <div class="image-upload-section">
                                        <div class="upload-area" id="uploadArea">
                                            <div class="upload-placeholder">
                                                <i class="las la-cloud-upload-alt"></i>
                                                <h6>@lang('Drag & Drop Images Here')</h6>
                                                <p class="text-muted mb-2">@lang('or')</p>
                                                <label class="btn btn--base btn-sm">
                                                    <i class="las la-folder-open me-1"></i> @lang('Browse Files')
                                                    <input type="file" name="images[]" id="imageInput" multiple accept="image/*" class="d-none">
                                                </label>
                                                <p class="text-muted mt-3 small">
                                                    @lang('Upload up to') {{ $marketplaceSettings['max_images_per_listing'] ?? 10 }} @lang('images') 
                                                    • @lang('Max 2MB each') • @lang('JPG, PNG, GIF')
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" name="primary_image_index" id="primaryImageIndexInput" value="0">
                                        <p class="text-muted small mt-2 mb-2" id="primaryImageHint">
                                            <i class="las la-star text-warning"></i> @lang('Click "Set as primary" on the image you want as the main thumbnail. Default: first image.')
                                        </p>
                                        <div class="image-preview-grid mt-3" id="imagePreview">
                                            {{-- Pre-loaded images from draft will go here --}}
                                        </div>
                                        
                                        <div class="alert alert-light border mt-4 image-tips-other">
                                            <h6 class="mb-2"><i class="las la-lightbulb text-warning me-2"></i>@lang('Image Tips')</h6>
                                            <ul class="mb-0 small">
                                                <li><strong>@lang('Select which image is the primary (main thumbnail)')</strong> - @lang('use "Set as primary" on your best image')</li>
                                                <li>@lang('Recommended dimensions: 1920x1080px (16:9) or 1600x900px. Images will be automatically resized to fit display cards (200px height)')</li>
                                                <li>@lang('Include screenshots of traffic stats, revenue proof, and key features')</li>
                                                <li>@lang('Use high-resolution images for crisp display on all devices')</li>
                                            </ul>
                                        </div>
                                        <div class="alert alert-light border mt-4 image-tips-domain" style="display:none;">
                                            <h6 class="mb-2"><i class="las la-lightbulb text-warning me-2"></i>@lang('Image Tips')</h6>
                                            <p class="mb-0 small">@lang('Images are optional for domain listings. You can add a logo or a relevant graphic.')</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-actions mt-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline--secondary btn-prev" data-step="5">
                                            <i class="las la-arrow-left me-2"></i> @lang('Back')
                                        </button>
                                        <button type="submit" class="btn btn--base fw-bold" id="submitListingBtn">
                                            <i class="las la-check-circle me-1"></i> @lang('Submit Listing')
                                        </button>
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/templates/basic/css/listing-form.css') }}">
@endpush

@push('script')
<script>
    // Global notification helper (assuming this exists or is provided by the template)
    function notify(status, message) {
        // This is a placeholder for your actual notification function (e.g., toastr, sweetalert, etc.)
        console.log(`[${status.toUpperCase()}] ${message}`);
        // Example: if using a simple alert
        // alert(`[${status.toUpperCase()}] ${message}`);
        
        // Example with Bootstrap alert for in-page display (optional)
        const alertHtml = `<div class="alert alert-${status === 'error' ? 'danger' : status}" role="alert">
            <i class="las la-${status === 'success' ? 'check-circle' : status === 'error' ? 'times-circle' : 'info-circle'} me-2"></i>
            ${message}
        </div>`;
        
        // Temporarily display the alert on top of the form
        const container = $('#listingForm').closest('.card-body');
        container.prepend(alertHtml);
        setTimeout(() => {
            container.find('.alert').first().slideUp(300, function() {
                $(this).remove();
            });
        }, 5000);
    }


    /**
     * Core Multi-Step Form Logic (ListingFormController)
     */
    const ListingFormController = {
        currentStep: {{ $currentStage ?? 1 }},
        maxStep: 6,
        draftSaveUrl: '{{ route("user.listing.draft.save") }}',
        draftClearUrl: '{{ route("user.listing.draft.clear") }}',
        hasDraft: {{ !empty($draftData) ? 'true' : 'false' }},
        saveDraftTimeout: null,
        isSaving: false,
        
        init: function() {
            // Bind events first
            this.bindEvents();
            this.initConfidentialityToggle();
            this.initMonetizationToggle();
            this.initPayoutMethodToggle();
            this.initSaleTypeToggle();
            this.initImageUpload();
            
            // Show the current step after DOM is ready
            requestAnimationFrame(() => {
                this.showStep(this.currentStep, false);
                // Initialize asset type fields after step is shown
                this.initAssetTypeFields();
                
                // Auto-save logic
                if (this.currentStep > 1) {
                    this.startAutoSave();
                }
            });
        },

        bindEvents: function() {
            const self = this;

            // Navigation buttons - use event delegation to work with dynamically shown/hidden buttons
            $(document).on('click', '.btn-next', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const nextStep = parseInt($(this).data('step')) || parseInt($(this).attr('data-step'));
                if (nextStep && !isNaN(nextStep)) {
                    self.handleNavigation(self.currentStep, nextStep);
                }
                return false;
            });

            $(document).on('click', '.btn-prev', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const prevStep = parseInt($(this).data('step')) || parseInt($(this).attr('data-step'));
                if (prevStep && !isNaN(prevStep)) {
                    self.handleNavigation(self.currentStep, prevStep);
                }
                return false;
            });

            // Business Type Selection (Step 1)
            $('input[name="business_type"]').on('change', function() {
                self.handleAssetTypeChange($(this).val());
                self.triggerInputEvent(); // Trigger save on change
            });

            // Asset Detail Inputs (Step 2) for triggering Validation Check
            $('#domainNameInput, #websiteUrlInput, #socialPlatformSelect, #socialUsernameInput, #socialUrlInput').on('input change blur', function() {
                // Throttle this event to avoid excessive calls
                clearTimeout(self.assetDetailTimeout);
                self.assetDetailTimeout = setTimeout(() => {
                    // Notify the validation module about a potential asset change
                    const assetInfo = self.getCurrentAssetInfo();
                    $(document).trigger('assetDetailChange', assetInfo);
                    
                    // If on Step 2 and URL is entered, show validation preview
                    if (self.currentStep === 2 && assetInfo.primaryAssetUrl) {
                        const requiresValidation = ['domain', 'website', 'social_media_account'];
                        if (requiresValidation.includes(assetInfo.businessType)) {
                            // Show a preview message that verification will be needed
                            if ($('#validationPreviewMessage').length === 0) {
                                $('#ownershipValidationSection').after('<div id="validationPreviewMessage" class="alert alert-info mt-3"><i class="las la-info-circle me-2"></i>Ownership verification will be required in the next step.</div>');
                            }
                        }
                    }
                }, 300);
                self.triggerInputEvent();
            });
            
            // Other form inputs for auto-save
            $('#listingForm').on('input change', 'input:not([type="file"]), select, textarea', function() {
                // Only trigger auto-save for later steps or if type/asset info changes
                if (self.currentStep >= 4 || $(this).attr('name') === 'business_type') {
                    self.triggerInputEvent();
                }
            });

            // Clear Draft button - use event delegation
            $(document).on('click', '#clearDraftBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.clearDraft();
                return false;
            });
        },

        // --- Core Flow Management ---

        handleNavigation: function(current, next) {
            const self = this;
            
            // Validate inputs
            if (isNaN(current) || isNaN(next) || current < 1 || next < 1 || next > this.maxStep) {
                console.error('Invalid navigation:', { current, next, maxStep: this.maxStep });
                return;
            }
            
            if (next > current) {
                // Moving forward: Validate current step first
                if (this.validateStep(current)) {
                    // Check for conditional skip on Step 2 -> 3
                    if (current === 2) {
                        const isTestUser = $('#listingForm').data('is-test-user') == 1 || $('#listingForm').data('is-test-user') === '1';
                        const businessType = $('input[name="business_type"]:checked').val();
                        const requiresValidation = ['domain', 'website', 'social_media_account'];
                        if (isTestUser || !requiresValidation.includes(businessType)) {
                            // Skip Step 3: test users skip verification; other types don't require it
                            next = 4;
                        }
                    }
                    
                    // Check for mandatory verification on Step 3 -> 4 (skip for test users)
                    if (current === 3) {
                         const isTestUser = $('#listingForm').data('is-test-user') == 1 || $('#listingForm').data('is-test-user') === '1';
                         if (!isTestUser) {
                             const requiresValidation = ['domain', 'website', 'social_media_account'];
                             const businessType = $('input[name="business_type"]:checked').val();
                             if (requiresValidation.includes(businessType)) {
                                if ($('#isVerifiedInput').val() !== '1') {
                                    notify('error', 'Please verify ownership before continuing.');
                                    const validationSection = $('#ownershipValidationSection');
                                    if (validationSection.length && validationSection.offset()) {
                                        $('html, body').animate({
                                            scrollTop: validationSection.offset().top - 100
                                        }, 300);
                                    }
                                    return;
                                }
                             }
                         }
                    }
                    
                    this.showStep(next, true);
                    this.currentStep = next;
                    if (this.currentStep >= 4) {
                        this.startAutoSave();
                    }
                }
            } else if (next < current) {
                // Moving backward: Just navigate
                this.showStep(next, true);
                this.currentStep = next;
                if (this.currentStep < 4) {
                     this.stopAutoSave();
                }
            }
        },

        showStep: function(step, animate = true) {
            // Validate step number
            if (isNaN(step) || step < 1 || step > this.maxStep) {
                console.error('Invalid step number:', step);
                return;
            }
            
            // Hide all steps first - use both methods to ensure they're hidden
            $('.form-step').each(function() {
                $(this).addClass('d-none').hide().css('display', 'none');
            });
            
            // Find target step
            const targetStep = $(`.form-step[data-step="${step}"]`);
            
            if (targetStep.length === 0) {
                console.error('Step not found:', step);
                return;
            }
            
            // Remove d-none class and show the step
            targetStep.removeClass('d-none');
            
            // Force display immediately
            targetStep.css({
                'display': 'block',
                'opacity': '1',
                'visibility': 'visible'
            });
            
            // Use requestAnimationFrame to ensure DOM is ready, then show child elements
            requestAnimationFrame(() => {
                // Ensure all child elements are visible
                targetStep.find('.step-header, .step-actions').css({
                    'display': '',
                    'opacity': '',
                    'visibility': ''
                });
                
                // Trigger a reflow to ensure styles are applied
                targetStep[0].offsetHeight;
            });

            // Update progress bar
            $('.progress-steps .step').removeClass('active completed');
            for (let i = 1; i <= this.maxStep; i++) {
                const stepElement = $(`.progress-steps .step[data-step="${i}"]`);
                if (i < step) {
                    stepElement.addClass('completed');
                } else if (i === step) {
                    stepElement.addClass('active');
                }
            }

            // Trigger step change event for other controllers
            $(document).trigger('stepChange', step);

            // Handle conditional field display based on step
            const businessType = $('input[name="business_type"]:checked').val();
            
            // Step 2: Show appropriate asset fields
            if (step === 2 && businessType) {
                this.handleAssetTypeChange(businessType);
            }
            
            // Step 4: Filter category dropdown to only show categories for the selected business type
            if (step === 4 && businessType) {
                this.filterCategoriesByBusinessType(businessType);
            }

            // Step 5: Handle sale type display
            if (step === 5) {
                const saleType = $('input[name="sale_type"]:checked').val() || 'fixed_price';
                this.handleSaleTypeChange(saleType);
            }
            
            // Step 3: Handle verification content
            if (step === 3) {
                const assetInfo = this.getCurrentAssetInfo();
                $(document).trigger('assetDetailChange', assetInfo);
            }

            // Step 6: Handle media content based on type
            if (step === 6) {
                if (businessType === 'domain') {
                    $('.domain-info-message').removeClass('d-none').show();
                    $('.image-upload-section').hide().addClass('d-none');
                } else {
                    $('.domain-info-message').addClass('d-none').hide();
                    $('.image-upload-section').removeClass('d-none').show();
                }
            }
            
            // Scroll to top of card body after a brief delay to ensure content is rendered
            setTimeout(() => {
                const card = $('#listingForm').closest('.card');
                if (card.length && card.offset()) {
                    $('html, body').animate({
                        scrollTop: card.offset().top - 20
                    }, 300);
                }
            }, 100);
        },

        validateStep: function(step) {
            let isValid = true;
            const currentStep = $(`.form-step[data-step="${step}"]`);
            
            // Clear previous errors
            currentStep.find('.is-invalid').removeClass('is-invalid');
            currentStep.find('.invalid-feedback').remove();

            if (step === 1) {
                // Check if a business type is selected
                if (!$('input[name="business_type"]:checked').length) {
                    notify('error', 'Please select a business type.');
                    isValid = false;
                }
            } else if (step === 2) {
                const businessType = $('input[name="business_type"]:checked').val();
                
                if (businessType === 'domain') {
                    const domainInput = $('#domainNameInput');
                    if (!domainInput.val() || !domainInput.val().trim()) {
                        domainInput.addClass('is-invalid');
                        domainInput.closest('.mb-3').append('<div class="invalid-feedback">Domain name is required.</div>');
                        isValid = false;
                    } else if (!domainInput.val().trim().match(/^https?:\/\//i)) {
                        domainInput.addClass('is-invalid');
                        domainInput.closest('.mb-3').append('<div class="invalid-feedback">Domain must start with http:// or https://</div>');
                        isValid = false;
                    }

                    // Domain registrar + expiry are mandatory for domain listings
                    const registrarInput = $('#domainRegistrarInput');
                    const expiryInput = $('#domainExpiryInput');
                    if (!registrarInput.val() || !registrarInput.val().trim()) {
                        registrarInput.addClass('is-invalid');
                        registrarInput.closest('.col-md-6').append('<div class="invalid-feedback">Domain registrar is required.</div>');
                        isValid = false;
                    }
                    if (!expiryInput.val() || !expiryInput.val().trim()) {
                        expiryInput.addClass('is-invalid');
                        expiryInput.closest('.col-md-6').append('<div class="invalid-feedback">Expiry date is required.</div>');
                        isValid = false;
                    } else {
                        const selectedDate = new Date(expiryInput.val());
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        if (selectedDate <= today) {
                            expiryInput.addClass('is-invalid');
                            expiryInput.closest('.col-md-6').append('<div class="invalid-feedback">Expiry date must be in the future (not expired).</div>');
                            isValid = false;
                        }
                    }
                } else if (businessType === 'website') {
                    const websiteInput = $('#websiteUrlInput');
                    if (!websiteInput.val() || !websiteInput.val().trim()) {
                        websiteInput.addClass('is-invalid');
                        websiteInput.closest('.mb-3').append('<div class="invalid-feedback">Website URL is required.</div>');
                        isValid = false;
                    } else if (!websiteInput.val().trim().match(/^https?:\/\//i)) {
                        websiteInput.addClass('is-invalid');
                        websiteInput.closest('.mb-3').append('<div class="invalid-feedback">Website URL must start with http:// or https://</div>');
                        isValid = false;
                    }

                    // Website registrar + expiry are mandatory
                    const registrarInput = $('#websiteRegistrarInput');
                    const expiryInput = $('#websiteExpiryInput');
                    if (!registrarInput.val() || !registrarInput.val().trim()) {
                        registrarInput.addClass('is-invalid');
                        registrarInput.closest('.col-md-6').append('<div class="invalid-feedback">Domain registrar is required.</div>');
                        isValid = false;
                    }
                    if (!expiryInput.val() || !expiryInput.val().trim()) {
                        expiryInput.addClass('is-invalid');
                        expiryInput.closest('.col-md-6').append('<div class="invalid-feedback">Domain expiry date is required.</div>');
                        isValid = false;
                    } else {
                        const selectedDate = new Date(expiryInput.val());
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        if (selectedDate <= today) {
                            expiryInput.addClass('is-invalid');
                            expiryInput.closest('.col-md-6').append('<div class="invalid-feedback">Domain expiry must be in the future (not expired).</div>');
                            isValid = false;
                        }
                    }
                } else if (businessType === 'social_media_account') {
                    const platform = $('#socialPlatformSelect');
                    const username = $('#socialUsernameInput');
                    if (!platform.val() || !platform.val().trim()) {
                        platform.addClass('is-invalid');
                        platform.closest('.col-md-6').append('<div class="invalid-feedback">Platform is required.</div>');
                        isValid = false;
                    }
                    if (!username.val() || !username.val().trim()) {
                        username.addClass('is-invalid');
                        username.closest('.col-md-6').append('<div class="invalid-feedback">Username/handle is required.</div>');
                        isValid = false;
                    }
                } else if (businessType === 'mobile_app') {
                    const appName = $('input[name="mobile_app_name"]');
                    if (!appName.val() || !appName.val().trim()) {
                        appName.addClass('is-invalid');
                        appName.closest('.col-md-6').append('<div class="invalid-feedback">App name is required.</div>');
                        isValid = false;
                    }
                } else if (businessType === 'desktop_app') {
                    const appName = $('input[name="desktop_app_name"]');
                    if (!appName.val() || !appName.val().trim()) {
                        appName.addClass('is-invalid');
                        appName.closest('.col-md-6').append('<div class="invalid-feedback">App name is required.</div>');
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                     notify('error', 'Please fill out all required asset details.');
                }
            } else if (step === 3) {
                 // The check for ownership verification is done inside handleNavigation to allow skipping for non-web assets
            } else if (step === 4) {
                // Check required fields (Category and Description)
                currentStep.find('select[name="listing_category_id"], textarea[name="description"]').each(function() {
                    if (!$(this).val() || $(this).val().trim() === '') {
                        $(this).addClass('is-invalid');
                        $(this).after('<div class="invalid-feedback">This field is required.</div>');
                        isValid = false;
                    }
                });
                 if (!isValid) {
                     notify('error', 'Please complete the Business Details section.');
                }
            } else if (step === 5) {
                const saleType = $('input[name="sale_type"]:checked').val();
                
                if (saleType === 'fixed_price') {
                    if (!$('#askingPriceInput').val() || parseFloat($('#askingPriceInput').val()) <= 0) {
                        $('#askingPriceInput').addClass('is-invalid');
                        $('#askingPriceInput').closest('.col-md-6').append('<div class="invalid-feedback">Asking price is required and must be greater than 0.</div>');
                        isValid = false;
                    }
                } else if (saleType === 'auction') {
                    if (!$('#startingBidInput').val() || parseFloat($('#startingBidInput').val()) <= 0) {
                        $('#startingBidInput').addClass('is-invalid');
                        $('#startingBidInput').closest('.col-md-6').append('<div class="invalid-feedback">Starting bid is required and must be greater than 0.</div>');
                        isValid = false;
                    }
                    if (!$('#bidIncrementInput').val() || parseFloat($('#bidIncrementInput').val()) <= 0) {
                        $('#bidIncrementInput').addClass('is-invalid');
                        $('#bidIncrementInput').closest('.col-md-4').append('<div class="invalid-feedback">Bid increment is required and must be greater than 0.</div>');
                        isValid = false;
                    }
                    if (!$('#auctionDurationSelect').val()) {
                        $('#auctionDurationSelect').addClass('is-invalid');
                        $('#auctionDurationSelect').closest('.col-md-4').append('<div class="invalid-feedback">Auction duration is required.</div>');
                        isValid = false;
                    }
                    // Reserve price validation (must be > starting bid if set)
                    const reservePrice = parseFloat($('#reservePriceInput').val() || 0);
                    const startingBid = parseFloat($('#startingBidInput').val() || 0);
                    if (reservePrice > 0 && reservePrice < startingBid) {
                         $('#reservePriceInput').addClass('is-invalid');
                         $('#reservePriceInput').closest('.col-md-6').append('<div class="invalid-feedback">Reserve price must be greater than or equal to the starting bid.</div>');
                         isValid = false;
                    }
                } else {
                     // Should not happen if a default is set, but as a fallback
                     notify('error', 'Please select a sale type.');
                     isValid = false;
                }
                 if (!isValid) {
                     notify('error', 'Please correct the pricing details.');
                }
            } else if (step === 6) {
                 // Image requirement for non-domain types
                 const businessType = $('input[name="business_type"]:checked').val();
                 if (businessType !== 'domain' && $('#imagePreview').children().length === 0 && $('#imageInput')[0].files.length === 0) {
                      notify('error', 'Please upload at least one image/screenshot for your listing.');
                      isValid = false;
                 }
            }

            return isValid;
        },

        // --- Conditional Field & Input Management ---

        handleAssetTypeChange: function(selectedType) {
            if (!selectedType) {
                // No type selected - hide all
                $('.business-fields').hide().addClass('d-none');
                $('#domainInputSection').hide().addClass('d-none');
                $('#websiteInputSection').hide().addClass('d-none');
                $('.website-business-metrics').hide().css('display', 'none');
                $('.monetization-section').hide().css('display', 'none');
                $('.monetization-options').hide().css('display', 'none');
                $('.monetization-other-wrap').hide().css('display', 'none');
                $('.financial-section').hide().css('display', 'none');
                return;
            }
            
            // Hide all business field sections first
            $('.business-fields').each(function() {
                $(this).hide().addClass('d-none').css('display', 'none');
            });
            $('#domainInputSection').hide().addClass('d-none').css('display', 'none');
            $('#websiteInputSection').hide().addClass('d-none').css('display', 'none');
            $('.website-business-metrics').hide().css('display', 'none');
            $('.monetization-section').hide().css('display', 'none');
            $('.monetization-options').hide().css('display', 'none');
            $('.monetization-other-wrap').hide().css('display', 'none');
            
            // Show/hide Financial Information section (hide for domain)
            if (selectedType === 'domain') {
                $('.financial-section').hide().css('display', 'none');
            } else {
                $('.financial-section').show().css('display', 'block');
            }

            // Description placeholder by business type
            const $desc = $('textarea[name="description"]');
            if ($desc.length) {
                const placeholder = selectedType === 'domain' ? $desc.data('placeholderDomain') : $desc.data('placeholderOther');
                if (placeholder) $desc.attr('placeholder', placeholder);
            }

            // Step 6 Image tips by type
            if (selectedType === 'domain') {
                $('.image-tips-domain').show().css('display', 'block');
                $('.image-tips-other').hide().css('display', 'none');
            } else {
                $('.image-tips-domain').hide().css('display', 'none');
                $('.image-tips-other').show().css('display', 'block');
            }

            // Show the appropriate section based on type
            requestAnimationFrame(() => {
                if (selectedType === 'domain') {
                    const domainSection = $('#domainInputSection');
                    domainSection.removeClass('d-none');
                    domainSection.css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible'
                    });
                    // Force reflow
                    if (domainSection[0]) domainSection[0].offsetHeight;
                } else if (selectedType === 'website') {
                    const websiteSection = $('#websiteInputSection');
                    websiteSection.removeClass('d-none');
                    websiteSection.css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible'
                    });
                    // Force reflow
                    if (websiteSection[0]) websiteSection[0].offsetHeight;

                    // Show website-only metrics section (in business details step)
                    $('.website-business-metrics').show().css('display', 'block');

                    // Show monetization section + website options
                    $('.monetization-section').show().css('display', 'block');
                    $('.monetization-options').hide().css('display', 'none');
                    $('.monetization-website').show().css('display', 'block');
                } else {
                    // Social media, mobile app, desktop app
                    const targetFields = $(`.business-fields.${selectedType}-fields`);
                    targetFields.removeClass('d-none');
                    targetFields.css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible'
                    });
                    // Force reflow
                    if (targetFields[0]) targetFields[0].offsetHeight;

                    // Monetization for apps + social
                    if (['social_media_account', 'mobile_app', 'desktop_app'].includes(selectedType)) {
                        $('.monetization-section').show().css('display', 'block');
                        $('.monetization-options').hide().css('display', 'none');
                        $(`.monetization-${selectedType}`).show().css('display', 'block');
                    }
                }
            });
        },
        
        initAssetTypeFields: function() {
            // Display the correct asset field section on initial load
            const initialType = $('input[name="business_type"]:checked').val();
            if (initialType) {
                this.handleAssetTypeChange(initialType);
            }
        },

        filterCategoriesByBusinessType: function(businessType) {
            const $select = $('#listingCategory');
            if (!$select.length) return;
            const currentVal = $select.val();
            let currentOptionStillVisible = false;
            $select.find('option').each(function() {
                const $opt = $(this);
                const optType = $opt.data('type');
                if ($opt.val() === '') {
                    $opt.show().prop('disabled', false);
                    return;
                }
                if (optType === businessType) {
                    $opt.show().prop('disabled', false);
                    if ($opt.val() === currentVal) currentOptionStillVisible = true;
                } else {
                    $opt.hide().prop('disabled', true);
                }
            });
            if (currentVal && !currentOptionStillVisible) {
                $select.val('');
            }
        },
        
        handleSaleTypeChange: function(selectedSaleType) {
            if (!selectedSaleType) {
                // Default to fixed_price if nothing selected
                selectedSaleType = 'fixed_price';
            }
            
            // Hide all pricing fields
            $('.pricing-fields').each(function() {
                $(this).hide().css('display', 'none');
            });
            
            // Show relevant pricing fields
            requestAnimationFrame(() => {
                if (selectedSaleType === 'fixed_price') {
                    const fixedPriceFields = $('.fixed-price-fields');
                    fixedPriceFields.css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible'
                    });
                    // Force reflow
                    if (fixedPriceFields[0]) fixedPriceFields[0].offsetHeight;
                } else if (selectedSaleType === 'auction') {
                    const auctionFields = $('.auction-fields');
                    auctionFields.css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible'
                    });
                    // Force reflow
                    if (auctionFields[0]) auctionFields[0].offsetHeight;
                }
            });
            
            // Update required attributes
            $('#askingPriceInput, #startingBidInput, #bidIncrementInput, #auctionDurationSelect').prop('required', false);
            if (selectedSaleType === 'fixed_price') {
                $('#askingPriceInput').prop('required', true);
            } else if (selectedSaleType === 'auction') {
                $('#startingBidInput, #bidIncrementInput, #auctionDurationSelect').prop('required', true);
            }
        },

        initConfidentialityToggle: function() {
            $('#isConfidential').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#ndaSection, #confidentialReasonSection').slideDown(300);
                } else {
                    $('#ndaSection, #confidentialReasonSection').slideUp(300);
                }
                ListingFormController.triggerInputEvent();
            });
            $('#requiresNda').on('change', function() {
                 ListingFormController.triggerInputEvent();
            });
        },

        initMonetizationToggle: function() {
            const toggleOther = () => {
                const businessType = $('input[name="business_type"]:checked').val();
                const supported = ['website', 'social_media_account', 'mobile_app', 'desktop_app'].includes(businessType);
                if (!supported) {
                    $('.monetization-other-wrap').hide();
                    return;
                }
                const hasOther = $('.monetization-options:visible .monetization-method-input[value="other"]').is(':checked');
                $('.monetization-other-wrap').toggle(!!hasOther);
            };

            $(document).on('change', '.monetization-method-input', function() {
                toggleOther();
                ListingFormController.triggerInputEvent();
            });

            // Initial state
            toggleOther();
        },

        initPayoutMethodToggle: function() {
            const toggle = () => {
                const method = $('input[name="payout_method"]:checked').val() || 'system';
                $('.direct-payout-fields').toggle(method === 'direct');
                if (method === 'direct') {
                    ListingFormController.quoteDirectPayoutFee();
                }
            };

            $(document).on('change', '.payout-method-radio', function() {
                toggle();
                ListingFormController.triggerInputEvent();
            });

            $(document).on('input change', '#askingPriceInput, #startingBidInput, #reservePriceInput, input[name="buy_now_price"]', function() {
                ListingFormController.quoteDirectPayoutFee();
            });

            toggle();
        },

        quoteDirectPayoutFee: function() {
            try {
                const saleType = $('input[name="sale_type"]:checked').val() || 'fixed_price';
                const payload = {
                    _token: '{{ csrf_token() }}',
                    sale_type: saleType,
                    asking_price: $('#askingPriceInput').val(),
                    starting_bid: $('#startingBidInput').val(),
                    reserve_price: $('#reservePriceInput').val(),
                    buy_now_price: $('input[name="buy_now_price"]').val(),
                };

                const $box = $('#directPayoutFeePreview');
                const $text = $('#directPayoutFeePreviewText');
                if (!$box.length || !$text.length) return;
                $text.text('@lang('Calculating...')');

                $.post('{{ route('user.listing.direct.fee.quote') }}', payload)
                    .done(function(res) {
                        const fee = Number(res.fee || 0);
                        const balance = Number(res.balance || 0);
                        const canAfford = !!res.can_afford;

                        const feeText = `{{ gs()->cur_sym }}${fee.toFixed(2)}`;
                        const balanceText = `{{ gs()->cur_sym }}${balance.toFixed(2)}`;

                        if (canAfford) {
                            $box.removeClass('is-danger');
                            $text.html(`@lang('Estimated') <span class="fw-bold">${feeText}</span> • @lang('Your wallet balance') <span class="fw-bold">${balanceText}</span>`);
                        } else {
                            $box.addClass('is-danger');
                            $text.html(`@lang('Estimated') <span class="fw-bold">${feeText}</span> • @lang('Your wallet balance') <span class="fw-bold">${balanceText}</span> • <strong>@lang('Insufficient balance')</strong>`);
                        }
                    })
                    .fail(function() {
                        $box.removeClass('is-danger');
                        $text.text("@lang('Unable to calculate right now. The exact fee will be checked when you submit.')");
                    });
            } catch (e) {
                // non-fatal
            }
        },
        
        initSaleTypeToggle: function() {
            const self = this;
            $('input[name="sale_type"]').on('change', function() {
                const selectedSaleType = $(this).val();
                self.handleSaleTypeChange(selectedSaleType);
                ListingFormController.triggerInputEvent();
                ListingFormController.quoteDirectPayoutFee();
            });
            
            // Initial state
            const initialSaleType = $('input[name="sale_type"]:checked').val() || 'fixed_price';
            this.handleSaleTypeChange(initialSaleType);
        },
        
        getCurrentAssetInfo: function() {
            const businessType = $('input[name="business_type"]:checked').val() || null;
            let primaryAssetUrl = '';
            let handle = '';
            
            if (businessType === 'domain') {
                primaryAssetUrl = $('#domainNameInput').val() || '';
            } else if (businessType === 'website') {
                primaryAssetUrl = $('#websiteUrlInput').val() || '';
            } else if (businessType === 'social_media_account') {
                const platform = $('#socialPlatformSelect').val();
                const username = $('#socialUsernameInput').val();
                const url = $('#socialUrlInput').val();
                handle = (username || '').trim();
                primaryAssetUrl = url || (platform && username ? platform + '/' + username : '');
            }
            // For apps, verification is not required, so primaryAssetUrl is less critical here.

            return {
                businessType: businessType,
                primaryAssetUrl: primaryAssetUrl.trim(),
                platform: $('#socialPlatformSelect').val(),
                handle: handle
            };
        },

        // --- Image Upload Management ---
        
        initImageUpload: function() {
            const self = this;
            this.accumulatedFiles = this.accumulatedFiles || [];

            // Handle file input change (each selection adds to list, like edit listing flow)
            $('#imageInput').on('change', function() {
                self.handleFileSelect(this.files);
                self.triggerInputEvent();
            });

            // Handle drag and drop
            const uploadArea = $('#uploadArea');
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                uploadArea.css('border-color', '#007bff');
            }).on('dragleave', function(e) {
                e.preventDefault();
                uploadArea.css('border-color', '#ddd');
            }).on('drop', function(e) {
                e.preventDefault();
                uploadArea.css('border-color', '#ddd');
                self.handleFileSelect(e.originalEvent.dataTransfer.files);
                self.triggerInputEvent();
            });

            // Handle remove button click
            $('#imagePreview').on('click', '.remove-btn', function(e) {
                e.preventDefault();
                const item = $(this).closest('.image-preview-item');
                const index = parseInt(item.data('index'), 10);
                item.remove();
                self.syncFileInputAfterRemove(index);
                self.triggerInputEvent();
            });

            // Set as primary
            $('#imagePreview').on('click', '.set-primary-btn', function(e) {
                e.preventDefault();
                const index = parseInt($(this).closest('.image-preview-item').data('index'), 10);
                $('#primaryImageIndexInput').val(index);
                $('#imagePreview').find('.image-preview-item').removeClass('primary-badge-visible').find('.primary-badge').remove();
                $(this).closest('.image-preview-item').addClass('primary-badge-visible').prepend('<span class="primary-badge">Primary</span>');
                self.triggerInputEvent();
            });
            
            // Preload draft images (basic structure to avoid another loop)
            @if(!empty($draftData['images']))
                // This assumes your draft images are base64 or paths in a structured way
                // For this example, we'll just log them to remind a proper implementation is needed
                console.log('Draft images detected, proper restoration logic for imagePreview needed.');
            @endif
        },
        
        handleFileSelect: function(files) {
            const input = document.getElementById('imageInput');
            const maxImages = {{ $marketplaceSettings['max_images_per_listing'] ?? 10 }};
            // Use accumulated list so each selection adds (edit flow). input.files is already the new selection, so merging with it would duplicate or replace.
            const currentFiles = this.accumulatedFiles || [];
            const newFiles = Array.from(files || []);
            const combined = currentFiles.slice();
            for (let i = 0; i < newFiles.length; i++) {
                if (combined.length >= maxImages) {
                    notify('warning', `You can only upload up to ${maxImages} images.`);
                    break;
                }
                const file = newFiles[i];
                if (file.size > 2 * 1024 * 1024) {
                    notify('error', `File "${file.name}" is too large (max 2MB).`);
                    continue;
                }
                if (!file.type.match('image.*')) {
                    notify('error', `File "${file.name}" is not a valid image type.`);
                    continue;
                }
                combined.push(file);
            }
            const dt = new DataTransfer();
            for (let i = 0; i < combined.length; i++) dt.items.add(combined[i]);
            input.files = dt.files;
            this.accumulatedFiles = Array.from(input.files);
            this.rebuildPreviewsFromInput();
        },

        rebuildPreviewsFromInput: function() {
            const preview = $('#imagePreview');
            const input = document.getElementById('imageInput');
            const primaryIndex = parseInt($('#primaryImageIndexInput').val(), 10) || 0;
            preview.empty();
            if (!input.files || input.files.length === 0) return;
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                if (!file.type.match('image.*')) continue;
                const reader = new FileReader();
                reader.onload = (function(idx, primIdx) {
                    return function(e) {
                        const isPrimary = (idx === primIdx);
                        const html = `
                            <div class="image-preview-item" data-index="${idx}">
                                ${isPrimary ? '<span class="primary-badge">Primary</span>' : ''}
                                <img src="${e.target.result}" alt="Preview">
                                <button type="button" class="remove-btn" title="@lang('Remove')">&times;</button>
                                <button type="button" class="set-primary-btn" title="@lang('Set as primary thumbnail')">@lang('Set as primary')</button>
                            </div>
                        `;
                        preview.append(html);
                    };
                })(i, primaryIndex);
                reader.readAsDataURL(file);
            }
        },

        syncFileInputAfterRemove: function(removedIndex) {
            const input = document.getElementById('imageInput');
            if (!input.files || input.files.length === 0) return;
            const dt = new DataTransfer();
            const primaryIndex = parseInt($('#primaryImageIndexInput').val(), 10) || 0;
            for (let i = 0; i < input.files.length; i++) {
                if (i !== removedIndex) dt.items.add(input.files[i]);
            }
            input.files = dt.files;
            this.accumulatedFiles = Array.from(input.files);
            let newPrimary = primaryIndex;
            if (removedIndex < primaryIndex) newPrimary = primaryIndex - 1;
            else if (removedIndex === primaryIndex) newPrimary = 0;
            $('#primaryImageIndexInput').val(Math.max(0, newPrimary));
            this.rebuildPreviewsFromInput();
        },

        // --- Draft Management (Auto-Save/Clear) ---

        triggerInputEvent: function() {
            clearTimeout(this.saveDraftTimeout);
            this.saveDraftTimeout = setTimeout(() => {
                this.saveDraft();
            }, 1500); // Wait 1.5 seconds after last input
        },

        startAutoSave: function() {
             // Re-enable auto-save if it was stopped
             this.triggerInputEvent();
        },

        stopAutoSave: function() {
            clearTimeout(this.saveDraftTimeout);
        },

        saveDraft: function() {
            if (this.isSaving) return;
            this.isSaving = true;

            const formData = new FormData($('#listingForm')[0]);
            formData.append('current_stage', this.currentStep);
            
            // Only capture the files explicitly selected in step 6
            const imageInput = $('#imageInput')[0];
            if (imageInput.files.length > 0) {
                 for (let i = 0; i < imageInput.files.length; i++) {
                      formData.append('draft_images[]', imageInput.files[i]);
                 }
            }

            $.ajax({
                url: this.draftSaveUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: 15000,
                beforeSend: function() {
                    $('#draftStatusBadge').html('<i class="las la-spinner la-spin me-1"></i>Saving...');
                },
                success: function(response) {
                    ListingFormController.isSaving = false;
                    if (response.success) {
                        $('#draftStatusBadge').html('<i class="las la-save me-1"></i>Draft Saved');
                        $('#draftStatusBadge').removeClass('bg-warning bg-danger').addClass('bg-info');
                    } else {
                        $('#draftStatusBadge').html('<i class="las la-exclamation-triangle me-1"></i>Save Failed');
                        $('#draftStatusBadge').removeClass('bg-info bg-warning').addClass('bg-danger');
                        console.error('Draft save failed:', response.message);
                    }
                },
                error: function(xhr) {
                    ListingFormController.isSaving = false;
                    $('#draftStatusBadge').html('<i class="las la-times-circle me-1"></i>Save Error');
                    $('#draftStatusBadge').removeClass('bg-info bg-warning').addClass('bg-danger');
                    console.error('Draft save error:', xhr.responseText);
                }
            });
        },

        clearDraft: function() {
            const self = this;
             if (!confirm('Are you sure you want to clear your saved draft? This action cannot be undone.')) {
                 return;
             }

            $.ajax({
                url: this.draftClearUrl,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        notify('success', 'Draft cleared successfully. Reloading page...');
                        window.location.reload(); // Simplest way to reset the form state
                    } else {
                        notify('error', response.message || 'Failed to clear draft.');
                    }
                },
                error: function() {
                    notify('error', 'An error occurred while clearing the draft.');
                }
            });
        }
    };

    /**
     * Ownership Validation Logic (ValidationController)
     * This module listens for changes and manages the verification process.
     */
    const ValidationController = {
        verificationToken: $('#verificationTokenInput').val() || null,
        selectedMethod: $('#verificationMethodInput').val() || null,
        isVerified: $('#isVerifiedInput').val() === '1',
        primaryAssetUrl: $('#verificationAssetInput').val() || null,
        businessType: $('input[name="business_type"]:checked').val() || null,
        instructions: null,
        isLoadingMethods: false,
        isGeneratingToken: false,
        isValidating: false,
        methodsCache: null,

        init: function() {
            this.bindEvents();
            // Initial check on load, if we are on step 3
            if (ListingFormController.currentStep >= 2) {
                const assetInfo = ListingFormController.getCurrentAssetInfo();
                this.handleAssetChange(assetInfo);
            }
        },

        bindEvents: function() {
            const self = this;
            
            // Listen for asset detail changes from the form controller
            $(document).on('assetDetailChange', function(e, assetInfo) {
                self.handleAssetChange(assetInfo);
            });
            
            // Listen for step changes to display relevant UI
            $(document).on('stepChange', function(e, step) {
                 if (step === 3) {
                     // Use requestAnimationFrame to ensure step is fully rendered first
                     requestAnimationFrame(() => {
                         const assetInfo = ListingFormController.getCurrentAssetInfo();
                         self.handleAssetChange(assetInfo);
                     });
                 } else {
                     // Hide validation UI when not on step 3
                     $('#ownershipValidationSection').hide();
                     $('#verificationNotRequiredMessage').hide();
                     $('#validationPreviewMessage').remove();
                 }
            });

            // Generate token button
            $('#generateTokenBtn').on('click', function() {
                self.generateToken();
            });

            // Validate ownership button
            $('#validateOwnershipBtn').on('click', function() {
                self.validateOwnership();
            });

            // Method selection
            $(document).on('change', 'input[name="validation_method"]', function() {
                self.selectedMethod = $(this).val();
                self.renderActionButtons();
                // Update hidden field for draft saving
                $('#verificationMethodInput').val(self.selectedMethod);
                ListingFormController.triggerInputEvent();
                // Show instructions - use setTimeout to ensure DOM is ready
                setTimeout(() => {
                    self.showInstructions();
                }, 50);
            });
        },
        
        normalizeUrl: function(url) {
             if (!url) return '';
             return url.trim().toLowerCase().replace(/\/+$/, '');
        },

        handleAssetChange: function(assetInfo) {
            const { businessType, primaryAssetUrl } = assetInfo;
            const requiresValidation = ['domain', 'website', 'social_media_account'];
            
            // 1. Check if asset type or URL changed (check OLD values before updating)
            const oldBusinessType = this.businessType;
            const oldPrimaryAssetUrl = this.primaryAssetUrl;
            const normalizedOldUrl = this.normalizeUrl(oldPrimaryAssetUrl || '');
            const normalizedNewUrl = this.normalizeUrl(primaryAssetUrl || '');
            
            const typeChanged = oldBusinessType && oldBusinessType !== businessType;
            const urlChanged = normalizedOldUrl !== normalizedNewUrl;
            
            // 2. Update current state FIRST
            this.businessType = businessType;
            this.primaryAssetUrl = primaryAssetUrl;
            $('#verificationAssetInput').val(primaryAssetUrl);
            
            // 3. If URL changed and we have validation data, clear it immediately
            if (urlChanged && (this.verificationToken || this.instructions || this.methodsCache)) {
                // Clear cache and state when URL changes
                this.methodsCache = null;
                this.verificationToken = null;
                this.instructions = null;
                this.selectedMethod = null;
                this.isVerified = false;
                $('#verificationTokenInput').val('');
                $('#verificationMethodInput').val('');
                $('#isVerifiedInput').val('0');
                $('#validationInstructions').hide().empty();
                $('#validationResult').hide().empty();
                $('#instructionsContent').empty();
                
                // Clear session via AJAX (fire and forget)
                $.ajax({
                    url: '{{ route("user.ownership.validation.clear") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    async: true
                });
            }
            
            // 4. Render the UI for Step 3 if currently viewing it
            if (ListingFormController.currentStep === 3) {
                if (!requiresValidation.includes(businessType)) {
                    // Validation not required for this type
                    $('#ownershipValidationSection').hide();
                    $('#verificationNotRequiredMessage').show().css('display', 'block');
                } else if (!primaryAssetUrl || !primaryAssetUrl.trim()) {
                    // No URL provided yet
                    $('#ownershipValidationSection').show().css('display', 'block');
                    $('#validationStatus').hide();
                    $('#validationMethodsList').html('<div class="alert alert-warning"><i class="las la-exclamation-triangle me-2"></i>Please complete asset details in Step 2 first.</div>');
                    $('#generateTokenBtn, #validateOwnershipBtn').hide();
                    $('#verificationNotRequiredMessage').hide();
                } else {
                    // Show the validation section - always ensure it's visible
                    $('#ownershipValidationSection').show().css({
                        'display': 'block',
                        'opacity': '1',
                        'visibility': 'visible'
                    });
                    $('#verificationNotRequiredMessage').hide();
                    
                    // If reset is needed (type or URL changed), clear state first, then load methods
                    if ((typeChanged || urlChanged) && oldBusinessType && requiresValidation.includes(oldBusinessType)) {
                        this.clearValidationState(typeChanged ? 
                            'Business type changed. Please verify ownership again.' : 
                            'Asset URL changed. Please generate a new verification token.'
                        );
                        // After clearing, load methods
                        requestAnimationFrame(() => {
                            $('#ownershipValidationSection').show().css({
                                'display': 'block',
                                'opacity': '1',
                                'visibility': 'visible'
                            });
                            this.checkVerificationStatus();
                        });
                    } else {
                        // No reset needed, just check status
                        this.checkVerificationStatus();
                    }
                }
            } else if (ListingFormController.currentStep === 2) {
                // On Step 2, hide validation UI (preview message is handled separately)
                $('#ownershipValidationSection').hide();
                $('#verificationNotRequiredMessage').hide();
            }
        },

        checkVerificationStatus: function() {
            // Check if already verified
            if (this.isVerified) {
                this.showVerifiedStatus();
                return;
            }
            
            // If not verified, load methods and set up UI
            this.loadValidationMethods(() => {
                // After methods load, check if token exists to determine button state
                this.renderActionButtons();
                this.showInstructions(); // Show instructions if method/token were restored
            });
        },
        
        clearValidationState: function(message) {
            this.isVerified = false;
            this.verificationToken = null;
            this.selectedMethod = null;
            this.instructions = null;
            $('#verificationTokenInput').val('');
            $('#verificationMethodInput').val('');
            $('#isVerifiedInput').val('0');

            // Clear UI but keep section visible
            $('#validationStatus').hide();
            $('#validationMethodsList').html('<div class="text-center py-2"><i class="las la-spinner la-spin"></i> <small>Loading methods...</small></div>');
            $('#validationInstructions').hide();
            $('#validationResult').hide();
            $('#oauthLoginButtons').hide().empty();
            
            // Ensure the validation section is visible if on Step 3
            if (ListingFormController.currentStep === 3) {
                $('#ownershipValidationSection').show().css('display', 'block');
                $('#verificationNotRequiredMessage').hide();
            }
            
            // Force reload methods to clear method list/cache
            this.methodsCache = null;
            
            // Notify user and re-render action buttons
            if (message) {
                 notify('warning', message);
            }
            
            this.renderActionButtons();

            // Clear session via AJAX (fire and forget)
            $.ajax({
                url: '{{ route("user.ownership.validation.clear") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                async: true
            });
        },

        loadValidationMethods: function(callback) {
            const self = this;

            // Use cache if available and URL matches
            if (this.methodsCache && this.methodsCache.businessType === this.businessType && 
                this.methodsCache.primaryAssetUrl && 
                this.normalizeUrl(this.methodsCache.primaryAssetUrl) === this.normalizeUrl(this.primaryAssetUrl)) {
                this.renderMethods(this.methodsCache.methods);
                if (callback) callback();
                return;
            }
            
            if (this.isLoadingMethods || !this.businessType || !this.primaryAssetUrl) {
                if (callback) callback();
                return;
            }
            
            this.isLoadingMethods = true;
            $('#validationMethodsList').html('<div class="text-center py-2"><i class="las la-spinner la-spin"></i> <small>Loading methods...</small></div>');
            
            $.ajax({
                url: '{{ route("user.ownership.validation.methods") }}',
                method: 'GET',
                data: { 
                    business_type: this.businessType,
                    primary_asset_url: this.primaryAssetUrl,
                    platform: (this.businessType === 'social_media_account' ? ($('#socialPlatformSelect').val() || '') : '')
                },
                timeout: 8000, 
                cache: false,
                success: function(response) {
                    self.isLoadingMethods = false;
                    if (response.success && response.methods) {
                        // Cache methods with current asset URL
                        self.methodsCache = { 
                            businessType: self.businessType, 
                            methods: response.methods,
                            primaryAssetUrl: self.primaryAssetUrl
                        };
                        
                        // Only restore token/instructions from response if backend returned them
                        // Backend only returns instructions if URL matches (we fixed backend to check this)
                        // So if backend returns instructions, it's safe to use them
                        if (response.token && response.instructions) {
                            // Backend verified URL matches, so restore token and instructions
                            self.verificationToken = response.token;
                            $('#verificationTokenInput').val(response.token);
                            self.instructions = response.instructions;
                        } else if (response.token && !response.instructions) {
                            // Backend returned token but no instructions - this means URL doesn't match
                            // Clear old instructions/token if we had them
                            if (self.verificationToken || self.instructions) {
                                self.instructions = null;
                                self.verificationToken = null;
                                $('#verificationTokenInput').val('');
                            }
                        }
                        // If backend doesn't return token at all, don't clear existing instructions
                        // (they might have been just generated and not yet in session)
                        
                        self.renderMethods(response.methods);
                    } else {
                        notify('error', 'Failed to load validation methods: ' + (response.message || 'Unknown error'));
                        $('#validationMethodsList').html('<div class="alert alert-danger">Failed to load validation methods.</div>');
                    }
                    if (callback) callback();
                },
                error: function() {
                    self.isLoadingMethods = false;
                    notify('error', 'Failed to load validation methods. Server error or timeout.');
                    $('#validationMethodsList').html('<div class="alert alert-danger">Failed to load validation methods.</div>');
                    if (callback) callback();
                }
            });
        },

        renderMethods: function(methods) {
            const self = this;
            const container = $('#validationMethodsList');
            container.empty();
            $('#oauthLoginButtons').empty().hide();

            if (this.isVerified) return;

            $.each(methods, function(key, method) {
                if (key === 'oauth_login') {
                    // Show OAuth login buttons if platform is selected
                    const platform = ListingFormController.getCurrentAssetInfo().platform;
                    if (platform) {
                         const oauthButtonsHtml = self.renderOAuthButtons(platform);
                         $('#oauthLoginButtons').html(oauthButtonsHtml).show();
                    }
                } else {
                    const isChecked = (self.selectedMethod === key) ? 'checked' : '';
                    const methodHtml = `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="validation_method" 
                                    id="method_${key}" value="${key}" ${isChecked}>
                            <label class="form-check-label" for="method_${key}">
                                <strong>${method.name}</strong>
                                <small class="d-block text-muted">${method.description}</small>
                            </label>
                        </div>
                    `;
                    container.append(methodHtml);
                }
            });
            
             // Re-select method if it was restored from draft/session
             if (this.selectedMethod) {
                 $(`input[name="validation_method"][value="${this.selectedMethod}"]`).prop('checked', true);
                 // Show instructions if method is selected and instructions exist
                 this.showInstructions();
             }
        },
        
        renderOAuthButtons: function(platform) {
             const self = this;
             const assetInfo = ListingFormController.getCurrentAssetInfo();
             const oauthUrl = '{{ route("user.ownership.validation.oauth.redirect", ":platform") }}'.replace(':platform', platform.toLowerCase());
             
             return `
                 <div class="alert alert-info d-flex align-items-center mb-3">
                     <i class="lab la-${platform.toLowerCase()} me-2 fs-4"></i>
                     <div>@lang('Use the button below to securely login and verify your account.')</div>
                 </div>
                 <a href="${oauthUrl}?business_type=${self.businessType}&handle=${encodeURIComponent(assetInfo.handle || '')}&token=${self.verificationToken || ''}&asset_url=${encodeURIComponent(assetInfo.primaryAssetUrl)}" 
                     class="btn btn-primary btn-lg">
                      <i class="las la-sign-in-alt me-1"></i>@lang('Login with') ${platform}
                 </a>
             `;
        },

        renderActionButtons: function() {
            if (this.isVerified) {
                $('#generateTokenBtn, #validateOwnershipBtn').hide();
                return;
            }

            if (!this.selectedMethod) {
                // If no method selected, only show token button if asset is ready
                if (this.primaryAssetUrl) {
                    $('#generateTokenBtn').show();
                    $('#validateOwnershipBtn').hide();
                } else {
                    $('#generateTokenBtn, #validateOwnershipBtn').hide();
                }
            } else if (this.selectedMethod === 'oauth_login') {
                 // For OAuth, the action is the large login button which is controlled by renderMethods/oauthLoginButtons
                 $('#generateTokenBtn, #validateOwnershipBtn').hide();
            } else if (this.verificationToken) {
                $('#generateTokenBtn').hide();
                $('#validateOwnershipBtn').show();
            } else {
                $('#generateTokenBtn').show();
                $('#validateOwnershipBtn').hide();
            }
        },

        generateToken: function() {
            const self = this;
            
            // Get fresh asset info from form
            const assetInfo = ListingFormController.getCurrentAssetInfo();
            if (!assetInfo.businessType || !assetInfo.primaryAssetUrl) {
                notify('error', 'Please complete asset details in Step 2 first.');
                return;
            }
            
            // Update state from form
            this.businessType = assetInfo.businessType;
            this.primaryAssetUrl = assetInfo.primaryAssetUrl;
            
            if (this.isGeneratingToken) {
                notify('error', 'Token generation is already in progress. Please wait.');
                return;
            }
            
            // Check if URL changed since last token (if token existed)
            if (this.verificationToken && this.normalizeUrl(this.primaryAssetUrl) !== this.normalizeUrl($('#verificationAssetInput').val())) {
                 this.clearValidationState('Asset URL changed. Generating new token.');
                 // The recursive call will run the generation now
            }
            
            this.isGeneratingToken = true;
            const btn = $('#generateTokenBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>Generating...');
            $('#validationResult').hide();

            $.ajax({
                url: '{{ route("user.ownership.validation.generate.token") }}',
                method: 'POST',
                data: {
                    business_type: this.businessType,
                    primary_asset_url: this.primaryAssetUrl,
                    platform: (this.businessType === 'social_media_account' ? ($('#socialPlatformSelect').val() || '') : ''),
                    _token: '{{ csrf_token() }}'
                },
                timeout: 10000,
                success: function(response) {
                    self.isGeneratingToken = false;
                    btn.prop('disabled', false).html(originalHtml);

                    if (response.success) {
                        console.log('Token generated successfully', {
                            token: response.token,
                            instructions: response.instructions,
                            selectedMethod: self.selectedMethod
                        });
                        
                        self.verificationToken = response.token;
                        self.instructions = response.instructions;
                        $('#verificationTokenInput').val(response.token);
                        
                        // Re-render to update instructions and buttons
                        self.renderMethods(self.methodsCache ? self.methodsCache.methods : response.methods);
                        self.renderActionButtons();
                        
                        notify('success', 'Verification token generated. Please select a verification method and follow instructions.');
                        
                        // Show instructions if a method is already selected
                        // (renderMethods will also call showInstructions if method is selected)
                        if (self.selectedMethod) {
                            setTimeout(() => {
                                console.log('Calling showInstructions after token generation');
                                self.showInstructions();
                            }, 100);
                        } else {
                            console.log('No method selected yet, instructions will show when method is selected');
                        }
                        
                    } else {
                        notify('error', response.message || 'Failed to generate token');
                    }
                },
                error: function(xhr) {
                    self.isGeneratingToken = false;
                    btn.prop('disabled', false).html(originalHtml);
                    const message = xhr.responseJSON ? xhr.responseJSON.message : 'Server error while generating token.';
                    notify('error', message);
                }
            });
        },

        showInstructions: function() {
            console.log('showInstructions called', {
                selectedMethod: this.selectedMethod,
                hasInstructions: !!this.instructions,
                hasToken: !!this.verificationToken,
                instructions: this.instructions
            });
            
            // Check prerequisites
            if (!this.selectedMethod) {
                console.log('No method selected');
                $('#validationInstructions').hide();
                return;
            }
            
            if (!this.instructions || typeof this.instructions !== 'object') {
                console.log('No instructions object');
                $('#validationInstructions').hide();
                return;
            }
            
            if (!this.verificationToken) {
                console.log('No verification token');
                $('#validationInstructions').hide();
                return;
            }
            
            const methodInstructions = this.instructions[this.selectedMethod];
            console.log('Method instructions:', methodInstructions);
            
            if (methodInstructions && methodInstructions.steps && Array.isArray(methodInstructions.steps)) {
                let stepsHtml = '<ol class="mb-0">';
                methodInstructions.steps.forEach(function(step) {
                    // Replace placeholder with actual token
                    let finalStep = step.replace(/{TOKEN}/g, this.verificationToken);
                    stepsHtml += `<li>${finalStep}</li>`;
                }.bind(this)); // Bind 'this' to access verificationToken
                stepsHtml += '</ol>';
                
                // Update the existing heading with the method title, then add steps
                const title = methodInstructions.title || 'Verification Instructions';
                const $instructionsEl = $('#validationInstructions');
                const $headingEl = $instructionsEl.find('.alert-heading');
                const $contentEl = $('#instructionsContent');
                
                console.log('DOM elements found:', {
                    instructionsEl: $instructionsEl.length,
                    headingEl: $headingEl.length,
                    contentEl: $contentEl.length
                });
                
                if ($headingEl.length) {
                    $headingEl.text(title);
                }
                
                if ($contentEl.length) {
                    $contentEl.html(stepsHtml);
                    console.log('Content set:', $contentEl.html(), 'Length:', $contentEl.html().length);
                } else {
                    console.error('instructionsContent element not found! Creating it...');
                    // If content div doesn't exist, create it
                    if ($headingEl.length) {
                        $headingEl.after('<div id="instructionsContent"></div>');
                        $('#instructionsContent').html(stepsHtml);
                    } else {
                        $instructionsEl.append('<div id="instructionsContent">' + stepsHtml + '</div>');
                    }
                }
                
                // Check if parent is visible
                const $parent = $instructionsEl.parent();
                console.log('Parent visibility check:', {
                    parentVisible: $parent.is(':visible'),
                    parentDisplay: $parent.css('display'),
                    ownershipSectionVisible: $('#ownershipValidationSection').is(':visible')
                });
                
                // Force show with multiple methods to ensure it's visible
                // Remove inline style first, then set display
                $instructionsEl.removeAttr('style');
                $instructionsEl.css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1'
                });
                $instructionsEl.show();
                
                // Also add a class to ensure visibility
                $instructionsEl.addClass('d-block').removeClass('d-none');
                
                // Double-check after a brief delay
                setTimeout(() => {
                    const finalCheck = $('#validationInstructions');
                    console.log('Final visibility check:', {
                        isVisible: finalCheck.is(':visible'),
                        display: finalCheck.css('display'),
                        computedDisplay: window.getComputedStyle(finalCheck[0]).display,
                        html: finalCheck.html(),
                        contentHtml: $('#instructionsContent').html()
                    });
                }, 100);
            } else {
                console.log('Invalid method instructions structure', methodInstructions);
                $('#validationInstructions').hide();
            }
        },

        validateOwnership: function() {
            const self = this;
            
            // Get fresh asset info from form
            const assetInfo = ListingFormController.getCurrentAssetInfo();
            if (!assetInfo.businessType || !assetInfo.primaryAssetUrl) {
                notify('error', 'Please complete asset details in Step 2 first.');
                return;
            }
            
            // Update state from form
            this.businessType = assetInfo.businessType;
            this.primaryAssetUrl = assetInfo.primaryAssetUrl;
            
            if (this.isValidating || !this.verificationToken || !this.selectedMethod || this.selectedMethod === 'oauth_login') {
                notify('error', 'Cannot validate. Check token, method selection, and asset URL.');
                return;
            }

            this.isValidating = true;
            const btn = $('#validateOwnershipBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>Validating...');
            $('#validationResult').hide().empty();
            
            // Collect additional data if needed (e.g., social media handle or specific filename)
            const additionalData = {};
            if (this.businessType === 'social_media_account') {
                additionalData.platform = $('#socialPlatformSelect').val();
                additionalData.handle = $('#socialUsernameInput').val();
            }
            
            // Quick prompt for filename for the file_upload method
            if (this.selectedMethod === 'file_upload') {
                const filename = prompt('Enter the filename you uploaded (default: marketplace-verification.txt):', 'marketplace-verification.txt');
                if (!filename || filename.trim() === '') {
                     this.isValidating = false;
                     btn.prop('disabled', false).html(originalHtml);
                     notify('error', 'Filename is required for file upload verification.');
                     return;
                }
                additionalData.filename = filename.trim();
            }

            $.ajax({
                url: '{{ route("user.ownership.validation.validate") }}',
                method: 'POST',
                data: {
                    business_type: this.businessType,
                    primary_asset_url: this.primaryAssetUrl,
                    method: this.selectedMethod,
                    token: this.verificationToken,
                    additional_data: additionalData,
                    _token: '{{ csrf_token() }}'
                },
                timeout: 25000,
                success: function(response) {
                    self.isValidating = false;
                    btn.prop('disabled', false).html(originalHtml);

                    if (response.success) {
                        self.isVerified = true;
                        $('#isVerifiedInput').val('1');
                        self.showVerifiedStatus();
                        notify('success', response.message || 'Ownership verified successfully!');
                    } else {
                        self.isVerified = false;
                        $('#isVerifiedInput').val('0');
                        $('#validationResult').html(`<div class="alert alert-danger">${response.message || 'Ownership verification failed'}</div>`).show();
                        notify('error', response.message || 'Ownership verification failed');
                    }
                },
                error: function(xhr) {
                    self.isValidating = false;
                    btn.prop('disabled', false).html(originalHtml);
                    const message = xhr.responseJSON ? xhr.responseJSON.message : 'Server error or timeout during validation.';
                    $('#validationResult').html(`<div class="alert alert-danger">${message}</div>`).show();
                    notify('error', message);
                }
            });
        },
        
        showVerifiedStatus: function() {
             $('#validationStatus').show();
             $('#generateTokenBtn, #validateOwnershipBtn, #validationInstructions, #validationResult, #oauthLoginButtons').hide();
             $('#validationMethodsList').html('<div class="alert alert-success"><i class="las la-check-circle me-2"></i>Ownership has been verified.</div>');
        }
    };

    $(document).ready(function() {
        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }
        
        // Check if controllers are defined
        if (typeof ListingFormController === 'undefined') {
            console.error('ListingFormController is not defined!');
            return;
        }
        
        if (typeof ValidationController === 'undefined') {
            console.error('ValidationController is not defined!');
            return;
        }
        
        // 1. Initialize core form controller
        try {
            ListingFormController.init();
        } catch (error) {
            console.error('Error initializing ListingFormController:', error);
        }
        
        // 2. Initialize validation controller
        try {
            ValidationController.init();
        } catch (error) {
            console.error('Error initializing ValidationController:', error);
        }

        // 3. Initialize OAuth account data handler
        try {
            initOAuthAccountHandler();
        } catch (error) {
            console.error('Error initializing OAuth account handler:', error);
        }
        
        // 4. Setup global step change listener for the progress bar
        $(document).on('stepChange', function(e, step) {
             $(`.progress-steps .step`).removeClass('active completed');
             for (let i = 1; i <= ListingFormController.maxStep; i++) {
                 const stepElement = $(`.progress-steps .step[data-step="${i}"]`);
                 if (i < step) {
                     stepElement.addClass('completed');
                 } else if (i === step) {
                     stepElement.addClass('active');
                 }
             }
        });
    });

    // OAuth Account Data Handler
    function initOAuthAccountHandler() {
        const oauthSupportedPlatforms = @json($oauthSupportedPlatforms ?? []);
        const oauthAccountData = @json($oauthAccountData ?? null);

        // Handle platform selection change
        $('#socialPlatformSelect').on('change', function() {
            const platform = $(this).val();
            handlePlatformSelection(platform, oauthSupportedPlatforms);
        });

        // Handle "show manual input" link
        $(document).on('click', '#showManualInputLink', function(e) {
            e.preventDefault();
            $('#oauthLoginSection').hide();
            $('#manualInputSection').show();
            $('#usernameRequired').show();
            $('#socialUsernameInput').attr('required', true);
            $('#oauthConnectedInput').val('0');
        });

        // Auto-populate fields if OAuth data is available
        if (oauthAccountData && oauthAccountData.connected) {
            populateOAuthData(oauthAccountData);
            
            // Auto-advance to next step if we're on step 2 and data is populated
            if (typeof ListingFormController !== 'undefined' && ListingFormController.currentStep === 2) {
                // Wait a moment for fields to populate, then show success message
                setTimeout(function() {
                    if (typeof notify === 'function') {
                        notify('success', 'Account connected! Fields have been filled in. Click Continue to proceed.');
                    }
                }, 500);
            }
        }

        // Check initial platform selection
        const initialPlatform = $('#socialPlatformSelect').val();
        if (initialPlatform) {
            handlePlatformSelection(initialPlatform, oauthSupportedPlatforms);
        }
    }

    function handlePlatformSelection(platform, oauthSupportedPlatforms) {
        const supportsOAuth = oauthSupportedPlatforms.includes(platform);
        const oauthAccountData = @json($oauthAccountData ?? null);
        const isOAuthConnected = oauthAccountData && oauthAccountData.connected && oauthAccountData.platform === platform;

        if (supportsOAuth && !isOAuthConnected) {
            // Show OAuth login section
            $('#oauthLoginSection').show();
            $('#manualInputSection').show(); // Still show manual inputs but make username optional
            $('#usernameRequired').hide();
            $('#socialUsernameInput').removeAttr('required');
            renderOAuthButton(platform);
        } else {
            // Show manual input section
            $('#oauthLoginSection').hide();
            $('#manualInputSection').show();
            $('#usernameRequired').show();
            $('#socialUsernameInput').attr('required', true);
        }

        // If OAuth was used for this platform, show connected badge
        if (isOAuthConnected) {
            $('#oauthConnectedBadge').show();
            $('#oauthConnectedInput').val('1');
            $('#usernameRequired').hide();
            $('#socialUsernameInput').removeAttr('required');
        } else {
            $('#oauthConnectedBadge').hide();
            $('#oauthConnectedInput').val('0');
        }
    }

    function renderOAuthButton(platform) {
        const platformNames = {
            'instagram': 'Instagram',
            'facebook': 'Facebook',
            'twitter': 'Twitter',
            'linkedin': 'LinkedIn'
        };

        const platformName = platformNames[platform] || platform;
        const oauthUrl = '{{ route("user.listing.account.oauth.redirect", ":platform") }}'.replace(':platform', platform);
        
        const buttonHtml = `
            <a href="${oauthUrl}" class="btn btn-primary btn-lg w-100">
                <i class="lab la-${platform.toLowerCase()} me-2 fs-4"></i>
                @lang('Login with') ${platformName}
            </a>
        `;
        
        $('#oauthLoginButtonContainer').html(buttonHtml);
    }

    function populateOAuthData(data) {
        if (data.username) {
            $('#socialUsernameInput').val(data.username);
        }
        if (data.account_url) {
            $('#socialUrlInput').val(data.account_url);
        }
        $('#oauthConnectedInput').val('1');
        $('#oauthConnectedBadge').show();
        $('#usernameRequired').hide();
        $('#socialUsernameInput').removeAttr('required');
    }
</script>
@endpush