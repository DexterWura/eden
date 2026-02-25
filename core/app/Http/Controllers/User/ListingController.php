<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingView;
use App\Models\ListingImage;
use App\Models\ListingMetric;
use App\Models\MarketplaceSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\EscrowFeeCalculator;
use App\Models\Transaction;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Listings';
        $user = auth()->user();

        $listings = Listing::where('user_id', $user->id)
            ->with(['listingCategory', 'images'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->search, function ($q, $search) {
                return $q->search($search);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.listing.index', compact('pageTitle', 'listings'));
    }

    public function create()
    {
        $pageTitle = 'Create New Listing';
        $listingCategories = ListingCategory::active()->orderBy('sort_order')->get();
        $businessTypes = $this->getBusinessTypes();
        $platforms = $this->getPlatforms();
        $marketplaceSettings = MarketplaceSetting::getAllSettings();

        // Check if user just submitted a listing successfully (via session flag)
        // If so, clear any existing draft to start fresh
        if (session()->has('listing_submitted_successfully')) {
            session()->forget([
                'listing_draft',
                'listing_draft_stage',
                'listing_draft_updated_at',
                'listing_submitted_successfully'
            ]);
        }

        // Restore draft data from session
        $draftData = session('listing_draft', []);
        $currentStage = session('listing_draft_stage', 1);

        // Pass ownership validation session data to view
        $ownershipValidationData = [
            'is_verified' => session('ownership_verified', false),
            'verification_token' => session('ownership_verification_token'),
            'verification_method' => session('ownership_verification_method'),
            'verification_asset' => session('ownership_verification_asset'),
            'verification_business_type' => session('ownership_verification_business_type'),
            'verification_platform' => session('ownership_verification_platform'),
        ];

        // Pass OAuth account data if available
        $oauthAccountData = session('oauth_account_data', null);
        $oauthSupportedPlatforms = $this->getOAuthSupportedPlatforms();
        $isTestUser = auth()->user()->is_test_user ?? false;

        return view('Template::user.listing.create', compact(
            'pageTitle',
            'listingCategories',
            'businessTypes',
            'platforms',
            'marketplaceSettings',
            'draftData',
            'currentStage',
            'ownershipValidationData',
            'oauthAccountData',
            'oauthSupportedPlatforms',
            'isTestUser'
        ));
    }

    /**
     * Save draft listing data to session
     */
    public function saveDraft(Request $request)
    {
        $user = auth()->user();
        
        // Get all form data using input() which excludes files by default
        $draftData = $request->input();
        $currentStage = $request->input('current_stage', 1);

        // Remove CSRF token and any other unwanted fields
        unset($draftData['_token'], $draftData['_method']);
        
        // Recursively remove any UploadedFile instances that might have slipped through
        $draftData = $this->removeFilesFromArray($draftData);

        // Store in session
        session([
            'listing_draft' => $draftData,
            'listing_draft_stage' => (int)$currentStage,
            'listing_draft_updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully',
            'stage' => $currentStage
        ]);
    }

    /**
     * Recursively remove UploadedFile instances from an array
     */
    private function removeFilesFromArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $cleaned = [];
        foreach ($data as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                // Skip UploadedFile instances
                continue;
            } elseif (is_array($value)) {
                // Recursively clean arrays
                $cleaned[$key] = $this->removeFilesFromArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Clear draft listing data
     */
    public function clearDraft()
    {
        session()->forget([
            'listing_draft',
            'listing_draft_stage',
            'listing_draft_updated_at'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft cleared successfully'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $businessType = $request->business_type;
            $saleType = $request->sale_type;
            $user = auth()->user();

        // Basic validations
        if (!MarketplaceSetting::allowBusinessType($businessType)) {
            $notify[] = ['error', 'Selling ' . str_replace('_', ' ', $businessType) . 's is currently disabled'];
            return back()->withInput($request->input())->withNotify($notify);
        }

        if ($saleType === 'auction' && !MarketplaceSetting::allowAuctions()) {
            $notify[] = ['error', 'Auctions are currently disabled'];
            return back()->withInput($request->input())->withNotify($notify);
        }

        if ($saleType === 'fixed_price' && !MarketplaceSetting::allowFixedPrice()) {
            $notify[] = ['error', 'Fixed price sales are currently disabled'];
            return back()->withInput($request->input())->withNotify($notify);
        }

        $minDescription = MarketplaceSetting::minListingDescription();
        $maxAuctionDays = MarketplaceSetting::maxAuctionDays();
        $minAuctionDays = MarketplaceSetting::minAuctionDays();

        // Normalize URLs
        if ($request->has('domain_name') && $request->domain_name) {
            $request->merge(['domain_name' => normalizeUrl($request->domain_name)]);
        }
        if ($request->has('website_url') && $request->website_url) {
            $request->merge(['website_url' => normalizeUrl($request->website_url)]);
        }
        if ($request->has('social_url') && $request->social_url) {
            $request->merge(['social_url' => normalizeUrl($request->social_url)]);
        }

        // Rate limiting for listing creation
        $recentListings = Listing::where('user_id', $user->id)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        if ($recentListings >= 10) { // Max 10 listings per 24 hours
            $notify[] = ['error', 'You have reached the maximum number of listings you can create per day. Please try again tomorrow.'];
            return back()->withInput($request->input())->withNotify($notify);
        }

        // Sanitize and validate input data
        $this->sanitizeListingInput($request);

        // Comprehensive validation with business logic
        // Use input() instead of all() to exclude files, then merge files separately for validation
        $input = $request->input();
        if ($request->hasFile('images')) {
            $input['images'] = $request->file('images');
        }
        $validator = \Validator::make($input, [
            'title' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-\.\,\&\(\)\[\]]+$/',
            'tagline' => 'nullable|string|max:200',
            'description' => 'required|string|min:' . $minDescription . '|max:10000',
            'business_type' => 'required|in:domain,website,social_media_account,mobile_app,desktop_app',
            'sale_type' => 'required|in:fixed_price,auction',
            'payout_method' => 'required|in:system,direct',
            'pay_via' => 'required_if:payout_method,direct|nullable|in:1,2',
            'direct_payment_link' => 'required_if:payout_method,direct|nullable|url|max:500',
            'asking_price' => 'required_if:sale_type,fixed_price|nullable|numeric|min:1|max:999999999',
            'starting_bid' => 'required_if:sale_type,auction|nullable|numeric|min:1|max:999999999',
            'reserve_price' => 'nullable|numeric|min:0|max:999999999',
            'buy_now_price' => 'nullable|numeric|min:0|max:999999999',
            'bid_increment' => 'nullable|numeric|min:1|max:999999',
            'auction_duration' => 'required_if:sale_type,auction|nullable|integer|min:' . $minAuctionDays . '|max:' . $maxAuctionDays,
            'listing_category_id' => 'nullable|exists:listing_categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'domain_name' => 'required_if:business_type,domain|nullable|url|regex:/^https?:\/\/.+/i|max:500',
            'domain_registrar' => 'exclude_unless:business_type,domain|required|string|max:100',
            'domain_expiry' => 'exclude_unless:business_type,domain|required|date|after:today',
            'website_url' => 'required_if:business_type,website|nullable|url|regex:/^https?:\/\/.+/i|max:500',
            'website_domain_registrar' => 'exclude_unless:business_type,website|required|string|max:100',
            'website_domain_expiry' => 'exclude_unless:business_type,website|required|date|after:today',
            'platform' => 'exclude_unless:business_type,social_media_account|required|in:instagram,youtube,tiktok,twitter,facebook,linkedin,pinterest,snapchat,twitch',
            'social_username' => 'exclude_unless:business_type,social_media_account|required_without:oauth_connected|nullable|string|max:100|regex:/^[a-zA-Z0-9\._\-]+$/',
            'oauth_connected' => 'nullable|boolean',
            'social_url' => 'exclude_unless:business_type,social_media_account|required|url|regex:/^https?:\/\/.+/i|max:500',
            'followers_count' => 'exclude_unless:business_type,social_media_account|nullable|integer|min:0|max:10000000000',
            'engagement_rate' => 'exclude_unless:business_type,social_media_account|nullable|numeric|min:0|max:100',
            'social_niche' => 'exclude_unless:business_type,social_media_account|nullable|string|max:100',
            'business_location' => 'exclude_unless:business_type,website|nullable|string|max:150',
            'overall_churn_percent' => 'exclude_unless:business_type,website|nullable|numeric|min:0|max:100',
            'site_age_months' => 'exclude_unless:business_type,website|nullable|integer|min:0|max:6000',
            'monetization_methods' => 'exclude_unless:business_type,website,social_media_account,mobile_app,desktop_app|nullable|array',
            'monetization_methods.*' => 'string|max:50',
            'monetization_other' => 'nullable|string|max:255',
            'monthly_revenue' => 'nullable|numeric|min:0|max:999999999',
            'monthly_profit' => 'nullable|numeric|min:0|max:999999999',
            'monthly_visitors' => 'nullable|numeric|min:0|max:999999999',
            'is_confidential' => 'nullable|boolean',
            'requires_nda' => 'nullable|boolean',
            'confidential_reason' => 'nullable|string|max:1000',
        ], [
            'images.*.image' => 'The uploaded file must be an image (JPEG, PNG, JPG, or GIF).',
            'images.*.mimes' => 'The image must be a file of type: JPEG, PNG, JPG, or GIF. Your file type is not supported.',
            'images.*.max' => 'Each image must not be larger than 2MB (2048 KB).',
        ]);

        if ($validator->fails()) {
            return back()->withInput($request->input())->withErrors($validator)->withNotify([['error', 'Please correct the errors below.']]);
        }

        // Business logic validation
        $this->validateListingBusinessLogic($request, $user);

        $payoutMethod = $request->input('payout_method', 'system');
        $directFee = 0.0;
        if ($payoutMethod === 'direct') {
            // Direct payout listing fee (upfront) is calculated from pricing and must be paid before submission.
            // Uses admin-configured Marketplace Fees if present; otherwise falls back to legacy escrow charge settings.
            if ($saleType === 'fixed_price') {
                $feeBaseAmount = (float) $request->asking_price;
            } else {
                $feeBaseAmount = (float) ($request->buy_now_price ?: ($request->reserve_price ?: $request->starting_bid));
            }

            $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees($feeBaseAmount, 'direct_payout_listing_fee', 'system');
            $directFee = (float) ($feeBreakdown['seller'] ?? $feeBreakdown['total'] ?? 0);

            // When insufficient balance, allow pay_via=2 (pay via gateway) on create
            $payVia = (int) $request->input('pay_via', 1);
            if ($directFee > 0 && $user->balance < $directFee && $payVia !== 2) {
                $notify[] = ['error', 'Direct payout requires an upfront Direct payout listing fee of ' . showAmount($directFee) . '. Your balance is ' . showAmount($user->balance) . '. You can pay via gateway instead.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
        }

        // Check if verification is required for this business type

        // Extract domain/website info
        $domain = null;
        $url = null;

        if ($businessType === 'domain') {
            $url = $request->domain_name;
            $domain = extractDomain($url);
            if (!$domain) {
                $notify[] = ['error', 'Invalid domain format.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
            // Check duplicates
            $existing = Listing::where('domain_name', $domain)
                ->where('user_id', '!=', $user->id)
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            if ($existing) {
                $notify[] = ['error', 'A listing for this domain already exists.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
            $platformHost = platform_domain();
            if ($platformHost && $domain === $platformHost) {
                $notify[] = ['error', __('general.platform_domain_blocked')];
                return back()->withInput($request->input())->withNotify($notify);
            }
        }

        if ($businessType === 'website') {
            $url = $request->website_url;
            $domain = extractDomain($url);
            if (!$domain) {
                $notify[] = ['error', 'Invalid website URL format.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
            // Check duplicates
            $existing = Listing::where('url', $url)
                ->where('user_id', '!=', $user->id)
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            if ($existing) {
                $notify[] = ['error', 'A listing for this website already exists.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
        }

        if ($businessType === 'social_media_account') {
            $url = $request->social_url;
            // Check duplicates
            $existing = Listing::where('url', $url)
                ->where('user_id', '!=', $user->id)
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            if ($existing) {
                $notify[] = ['error', 'A listing for this social media account already exists.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
        }

        // Check ownership verification (mandatory for domain, website, social_media_account)
        // Skip verification for test users
        $requiresVerification = in_array($businessType, ['domain', 'website', 'social_media_account']);
        $ownerVerified = session()->get('ownership_verified', false);
        
        // Test users don't need ownership verification
        if ($requiresVerification && !$user->is_test_user && !$ownerVerified) {
            $notify[] = ['error', 'Ownership could not be verified. Please verify ownership before creating a listing.'];
            return back()->withInput($request->input())->withNotify($notify);
        }
        
        // For test users, automatically set owner_verified to true
        if ($user->is_test_user && $requiresVerification) {
            $ownerVerified = true;
        }
        
        // Get primary asset URL
        $primaryAssetUrl = null;
        if ($businessType === 'domain') {
            $primaryAssetUrl = $request->domain_name;
        } elseif ($businessType === 'website') {
            $primaryAssetUrl = $request->website_url;
        } elseif ($businessType === 'social_media_account') {
            $primaryAssetUrl = $request->social_url ?? ($request->platform . '/' . $request->social_username);
        }
        
        // Generate title
        $title = $this->generateTitle($request, $domain);

        $payVia = (int) $request->input('pay_via', 1); // 1 = wallet, 2 = pay via gateway (for direct payout fee on create)

        // Create listing + (optional) charge direct payout fee atomically.
        $listing = DB::transaction(function () use ($user, $request, $businessType, $saleType, $payoutMethod, $directFee, $payVia, $title, $domain, $primaryAssetUrl, $ownerVerified) {
            $listing = new Listing();
            $listing->listing_number = getTrx();
            $listing->user_id = $user->id;
            $listing->title = $title;
            $listing->slug = Str::slug($title) . '-' . Str::random(8);
            $listing->tagline = $request->tagline;
            $listing->description = $request->description;
            $listing->business_type = $businessType;
            $listing->sale_type = $saleType;
            $listing->payout_method = $payoutMethod;
            $listing->direct_payment_link = $payoutMethod === 'direct' ? $request->direct_payment_link : null;
            $listing->listing_category_id = $request->listing_category_id;
            $listing->is_confidential = $request->has('is_confidential') ? (bool)$request->is_confidential : false;
            $listing->requires_nda = $request->has('requires_nda') ? (bool)$request->requires_nda : false;
            $listing->confidential_reason = $request->confidential_reason ?? null;

            // Ownership validation fields
            $listing->primary_asset_url = $primaryAssetUrl;
            $listing->owner_verified = $ownerVerified;
            if ($ownerVerified) {
                $listing->ownership_verification_method = session()->get('ownership_verification_method');
                $listing->ownership_verified_at = now();
            }

            // Pricing
            if ($saleType === 'fixed_price') {
                $listing->asking_price = $request->asking_price;
            } else {
                $listing->starting_bid = $request->starting_bid;
                $listing->reserve_price = $request->reserve_price ?? 0;
                $listing->buy_now_price = $request->buy_now_price ?? 0;
                $listing->bid_increment = $request->bid_increment ?? 1;
                $listing->auction_duration_days = $request->auction_duration;
            }

            // Business fields
            $this->fillBusinessTypeFields($listing, $request);

            // Financials & Traffic
            $listing->monthly_revenue = $request->monthly_revenue ?? 0;
            $listing->monthly_profit = $request->monthly_profit ?? 0;
            $listing->yearly_revenue = $request->yearly_revenue ?? 0;
            $listing->yearly_profit = $request->yearly_profit ?? 0;
            $listing->monthly_visitors = $request->monthly_visitors ?? 0;
            $listing->monthly_page_views = $request->monthly_page_views ?? 0;
            $listing->traffic_sources = $request->traffic_sources;
            $listing->monetization_methods = $request->monetization_methods;
            $listing->monetization_other = $request->monetization_other;
            $listing->assets_included = $request->assets_included;
            $listing->business_location = $businessType === 'website' ? $request->business_location : null;
            $listing->overall_churn_percent = $businessType === 'website' ? $request->overall_churn_percent : null;
            $listing->site_age_months = $businessType === 'website' ? $request->site_age_months : null;

            // SEO
            $listing->meta_title = $request->meta_title ?? $title;
            $listing->meta_description = $request->meta_description ?? Str::limit(strip_tags($request->description), 160);

            $listing->status = Status::LISTING_PENDING;
            $listing->save();

            if ($businessType === 'domain' && $domain) {
                Cache::forget('listing_domain:' . $domain);
            }

            if ($payoutMethod === 'direct' && $directFee > 0) {
                $listing->direct_payout_fee = $directFee;
                if ($payVia === 2) {
                    // Pay via gateway: leave fee unpaid; redirect to checkout after transaction
                    $listing->direct_payout_fee_paid_at = null;
                    $listing->save();
                } else {
                    $trx = getTrx();
                    $lockedUser = \App\Models\User::lockForUpdate()->findOrFail($user->id);
                    if ($lockedUser->balance < $directFee) {
                        throw new \Exception('Insufficient balance for direct payout fee. You can pay via gateway instead.');
                    }
                    $lockedUser->balance -= $directFee;
                    $lockedUser->save();
                    $transaction = new Transaction();
                    $transaction->user_id = $lockedUser->id;
                    $transaction->amount = $directFee;
                    $transaction->post_balance = $lockedUser->balance;
                    $transaction->charge = 0;
                    $transaction->trx_type = '-';
                    $transaction->remark = 'direct_payout_fee';
                    $transaction->details = 'Direct payout listing fee (upfront) for listing #' . $listing->listing_number;
                    $transaction->trx = $trx;
                    $transaction->save();
                    $listing->direct_payout_fee_paid_at = now();
                    $listing->direct_payout_fee_trx = $trx;
                    $listing->save();
                }
            }

            // keep counter consistent with listing creation
            $lockedUser = \App\Models\User::lockForUpdate()->findOrFail($user->id);
            $lockedUser->increment('total_listings');

            return $listing;
        });

        // Handle images (normalize to array: single file upload comes as one element)
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            if (!is_array($files)) {
                $files = $files ? [$files] : [];
            }
            $primaryIndex = (int) $request->input('primary_image_index', 0);
            $this->uploadImages($listing, $files, $primaryIndex);
        }

        // Direct payout fee pay via gateway on create: redirect to checkout; fee is applied after payment success
        if ($payoutMethod === 'direct' && $directFee > 0 && $payVia === 2) {
            $checkout = [
                'type' => 'direct_payout_listing_fee',
                'amount' => $directFee,
                'listing_id' => (int) $listing->id,
            ];
            session()->put('checkout', encrypt($checkout));
            $notify[] = ['info', 'Complete payment to activate direct payout for this listing.'];
            return redirect()->route('user.deposit.index', ['type' => 'checkout'])->withNotify($notify);
        }

        // Log listing creation
        \Log::info('Listing created', [
            'listing_id' => $listing->id,
            'listing_number' => $listing->listing_number,
            'user_id' => $user->id,
            'username' => $user->username,
            'title' => $listing->title,
            'business_type' => $listing->business_type,
            'sale_type' => $listing->sale_type,
            'asking_price' => $listing->asking_price,
            'status' => $listing->status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Clear draft data after successful submission
        session()->forget([
            'listing_draft',
            'listing_draft_stage',
            'listing_draft_updated_at',
            'ownership_verified',
            'ownership_verification_token',
            'ownership_verification_asset',
            'ownership_verification_business_type',
            'ownership_verification_method',
            'ownership_verification_platform'
        ]);
        
        // Set flag to indicate successful submission (so create page knows to clear draft on next visit)
        session()->put('listing_submitted_successfully', true);

        $notify[] = ['success', 'Listing created successfully and submitted for review!'];
        if ($ownerVerified) {
            $notify[] = ['info', 'Ownership verification was completed successfully.'];
        }
        return redirect()->route('user.listing.index')->withNotify($notify);
        } catch (\Exception $e) {
            \Log::error('Listing creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'business_type' => $request->business_type ?? null,
                'sale_type' => $request->sale_type ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            $notify[] = ['error', 'An error occurred while creating your listing. Please try again.'];
            return back()->withInput($request->input())->withNotify($notify);
        }
    }

    private function generateTitle($request, $domain = null)
    {
        switch ($request->business_type) {
            case 'domain':
                return $domain ?: extractDomain($request->domain_name) ?: 'Domain Listing';
            case 'website':
                return $domain ?: extractDomain($request->website_url) ?: 'Website Listing';
            case 'social_media_account':
                $username = $request->social_username ?? '';
                if ($username) {
                    return '@' . $username;
                }
                return ucfirst($request->platform ?? 'Social Media Account');
            case 'mobile_app':
                return $request->mobile_app_name ?? 'Mobile App';
            case 'desktop_app':
                return $request->desktop_app_name ?? 'Desktop App';
            default:
                return ucfirst(str_replace('_', ' ', $request->business_type));
        }
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Listing';
        $listing = Listing::where('user_id', auth()->id())
            ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_REJECTED, Status::LISTING_ACTIVE])
            ->with(['images', 'metrics'])
            ->findOrFail($id);

        $listingCategories = ListingCategory::active()->orderBy('sort_order')->get();
        $businessTypes = $this->getBusinessTypes();
        $platforms = $this->getPlatforms();

        return view('Template::user.listing.edit', compact(
            'pageTitle',
            'listing',
            'listingCategories',
            'businessTypes',
            'platforms'
        ));
    }

    public function update(Request $request, $id)
    {
        try {
            $listing = Listing::where('user_id', auth()->id())
                ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_REJECTED, Status::LISTING_ACTIVE])
                ->findOrFail($id);

        // Normalize URLs if being updated
        if ($request->has('domain_name') && $request->domain_name) {
            $request->merge(['domain_name' => normalizeUrl($request->domain_name)]);
        }
        
        if ($request->has('website_url') && $request->website_url) {
            $request->merge(['website_url' => normalizeUrl($request->website_url)]);
        }

        $minDescription = MarketplaceSetting::minListingDescription();
        
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:' . $minDescription,
            'asking_price' => 'required_if:sale_type,fixed_price|nullable|numeric|min:1',
            'starting_bid' => 'required_if:sale_type,auction|nullable|numeric|min:1',
            'bid_increment' => 'nullable|numeric|min:1|max:999999',
            'payout_method' => 'required|in:system,direct',
            'direct_payment_link' => 'required_if:payout_method,direct|nullable|url|max:500',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'domain_name' => 'required_if:business_type,domain|nullable|url|regex:/^https?:\/\/.+/i',
            'website_url' => 'required_if:business_type,website|nullable|url|regex:/^https?:\/\/.+/i',
            'business_location' => 'exclude_unless:business_type,website|nullable|string|max:150',
            'overall_churn_percent' => 'exclude_unless:business_type,website|nullable|numeric|min:0|max:100',
            'site_age_months' => 'exclude_unless:business_type,website|nullable|integer|min:0|max:6000',
            'monetization_methods' => 'nullable|array',
            'monetization_methods.*' => 'string|max:50',
            'monetization_other' => 'nullable|string|max:255',
        ], [
            'domain_name.url' => 'Please enter a valid domain URL (e.g., https://example.com)',
            'website_url.url' => 'Please enter a valid website URL (e.g., https://example.com)',
            'images.*.image' => 'The uploaded file must be an image (JPEG, PNG, JPG, or GIF).',
            'images.*.mimes' => 'The image must be a file of type: JPEG, PNG, JPG, or GIF. Your file type is not supported.',
            'images.*.max' => 'Each image must not be larger than 2MB (2048 KB).',
        ]);

        // Conditional required fields based on the actual listing type (safer than relying on request business_type)
        if ($listing->business_type === 'domain') {
            $validator->addRules([
                'domain_registrar' => 'required|string|max:100',
                // Cannot list an expired domain
                'domain_expiry' => 'required|date|after:today',
            ]);
        }

        if ($listing->business_type === 'website') {
            $validator->addRules([
                'website_domain_registrar' => 'required|string|max:100',
                'website_domain_expiry' => 'required|date|after:today',
            ]);
        }

        if ($listing->sale_type === 'auction') {
            $validator->addRules([
                'bid_increment' => 'required|numeric|min:1|max:999999',
            ]);
        }

        if ($validator->fails()) {
            return back()->withInput($request->input())->withErrors($validator)->withNotify([['error', 'Please correct the errors below.']]);
        }
        
        // Check for duplicate domains/websites (excluding current listing)
        if ($listing->business_type === 'domain' && $request->domain_name) {
            $domain = extractDomain($request->domain_name);
            $existingListing = Listing::where('domain_name', $domain)
                ->where('id', '!=', $listing->id)
                ->where('user_id', '!=', auth()->id())
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            
            if ($existingListing) {
                $notify[] = ['error', 'A listing for this domain already exists. Each domain can only be listed once.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
            $platformHost = platform_domain();
            if ($platformHost && $domain === $platformHost) {
                $notify[] = ['error', __('general.platform_domain_blocked')];
                return back()->withInput($request->input())->withNotify($notify);
            }
        }
        
        if ($listing->business_type === 'website' && $request->website_url) {
            $url = normalizeUrl($request->website_url);
            $existingListing = Listing::where('url', $url)
                ->where('id', '!=', $listing->id)
                ->where('user_id', '!=', auth()->id())
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            
            if ($existingListing) {
                $notify[] = ['error', 'A listing for this website already exists. Each website can only be listed once.'];
                return back()->withInput($request->input())->withNotify($notify);
            }
        }

        $listing->title = $request->title;
        $listing->tagline = $request->tagline;
        $listing->description = $request->description;
        $listing->listing_category_id = $request->listing_category_id;

        // Confidential & NDA Settings
        $listing->is_confidential = $request->has('is_confidential') ? (bool)$request->is_confidential : false;
        $listing->requires_nda = $request->has('requires_nda') ? (bool)$request->requires_nda : false;
        $listing->confidential_reason = $request->confidential_reason ?? null;

        // Pricing with validation
        if ($listing->sale_type === 'fixed_price') {
            $listing->asking_price = $request->asking_price;
        } else {
            $listing->starting_bid = $request->starting_bid;
            $listing->reserve_price = $request->reserve_price ?? 0;
            $listing->buy_now_price = $request->buy_now_price ?? 0;
            $listing->bid_increment = $request->bid_increment ?? 1;
            
            // Common sense validations (same as create)
            if ($listing->buy_now_price > 0 && $listing->reserve_price > $listing->buy_now_price) {
                $notify[] = ['error', 'Reserve price cannot be higher than Buy Now price'];
                return back()->withInput($request->input())->withNotify($notify);
            }
            
            if ($listing->reserve_price > 0 && $listing->reserve_price < $listing->starting_bid) {
                $notify[] = ['error', 'Reserve price cannot be lower than starting bid'];
                return back()->withInput($request->input())->withNotify($notify);
            }
            
            if ($listing->buy_now_price > 0 && $listing->buy_now_price < $listing->starting_bid) {
                $notify[] = ['error', 'Buy Now price cannot be lower than starting bid'];
                return back()->withInput($request->input())->withNotify($notify);
            }
            if ($listing->starting_bid > 0 && $listing->bid_increment > $listing->starting_bid * 0.5) {
                $notify[] = ['error', 'Bid increment cannot be more than 50% of the starting bid'];
                return back()->withInput($request->input())->withNotify($notify);
            }
        }

        $oldDomainForCache = ($listing->business_type === 'domain' && $listing->domain_name) ? $listing->domain_name : null;

        // Business-specific fields
        $this->fillBusinessTypeFields($listing, $request);

        // Financials
        $listing->monthly_revenue = $request->monthly_revenue ?? 0;
        $listing->monthly_profit = $request->monthly_profit ?? 0;
        $listing->yearly_revenue = $request->yearly_revenue ?? 0;
        $listing->yearly_profit = $request->yearly_profit ?? 0;

        // Traffic
        $listing->monthly_visitors = $request->monthly_visitors ?? 0;
        $listing->monthly_page_views = $request->monthly_page_views ?? 0;
        $listing->traffic_sources = $request->traffic_sources;
        $listing->monetization_methods = $request->monetization_methods;
        $listing->monetization_other = $request->monetization_other;
        $listing->assets_included = $request->assets_included;
        $listing->business_location = $listing->business_type === 'website' ? $request->business_location : null;
        $listing->overall_churn_percent = $listing->business_type === 'website' ? $request->overall_churn_percent : null;
        $listing->site_age_months = $listing->business_type === 'website' ? $request->site_age_months : null;

        // SEO
        $listing->meta_title = $request->meta_title ?? $request->title;
        $listing->meta_description = $request->meta_description;

        // Re-submit for approval if was rejected
        if ($listing->status == Status::LISTING_REJECTED) {
            $listing->status = Status::LISTING_PENDING;
            $listing->rejection_reason = null;
        }

        $payoutMethod = $request->input('payout_method', 'system');
        $directFee = 0.0;
        $shouldChargeDirectFee = false;
        if ($payoutMethod === 'direct') {
            $feeBaseAmount = $listing->sale_type === 'fixed_price'
                ? (float) ($request->asking_price ?? $listing->asking_price)
                : (float) ($request->buy_now_price ?: $request->reserve_price ?: $request->starting_bid ?? $listing->starting_bid);
            $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees($feeBaseAmount, 'direct_payout_listing_fee', 'system');
            $directFee = (float) ($feeBreakdown['seller'] ?? $feeBreakdown['total'] ?? 0);
            $shouldChargeDirectFee = $directFee > 0 && !$listing->direct_payout_fee_paid_at;
        }

        // Handle new images before save/redirect so they are applied in all cases
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            if (!is_array($files)) {
                $files = $files ? [$files] : [];
            }
            $primaryIndex = (int) $request->input('primary_image_index', 0);
            $this->uploadImages($listing, $files, $primaryIndex);
        }

        if ($payoutMethod === 'direct' && $shouldChargeDirectFee) {
            $payVia = (int) $request->input('pay_via', 1); // 1 = wallet, 2 = pay via gateway
            if ($payVia === 2) {
                // Pay via gateway: save listing with fee unpaid, redirect to checkout
                $listing->direct_payout_fee = $directFee;
                $listing->direct_payout_fee_paid_at = null;
                $listing->save();
                $checkout = [
                    'type' => 'direct_payout_listing_fee',
                    'amount' => $directFee,
                    'listing_id' => (int) $listing->id,
                ];
                session()->put('checkout', encrypt($checkout));
                $notify[] = ['info', 'Complete payment to activate direct payout for this listing.'];
                return redirect()->route('user.deposit.index', ['type' => 'checkout'])->withNotify($notify);
            }
            // Wallet payment
            $trx = getTrx();
            DB::transaction(function () use ($listing, $directFee, $trx) {
                $listing->save();
                $lockedUser = \App\Models\User::lockForUpdate()->findOrFail(auth()->id());
                $lockedListing = Listing::lockForUpdate()->findOrFail($listing->id);
                if ($lockedUser->balance < $directFee) {
                    throw new \Exception('Insufficient balance for direct payout fee. You can pay via gateway instead.');
                }
                $lockedUser->balance -= $directFee;
                $lockedUser->save();
                $transaction = new Transaction();
                $transaction->user_id = $lockedUser->id;
                $transaction->amount = $directFee;
                $transaction->post_balance = $lockedUser->balance;
                $transaction->charge = 0;
                $transaction->trx_type = '-';
                $transaction->remark = 'direct_payout_fee';
                $transaction->details = 'Direct payout listing fee (upfront) for listing #' . $lockedListing->listing_number;
                $transaction->trx = $trx;
                $transaction->save();
                $lockedListing->direct_payout_fee = $directFee;
                $lockedListing->direct_payout_fee_paid_at = now();
                $lockedListing->direct_payout_fee_trx = $trx;
                $lockedListing->save();
            });
        } else {
            $listing->save();
        }

        if ($listing->business_type === 'domain') {
            if ($oldDomainForCache) {
                Cache::forget('listing_domain:' . $oldDomainForCache);
            }
            if ($listing->domain_name) {
                Cache::forget('listing_domain:' . $listing->domain_name);
            }
        }

        // Log listing update
        \Log::info('Listing updated', [
            'listing_id' => $listing->id,
            'listing_number' => $listing->listing_number,
            'user_id' => auth()->id(),
            'username' => auth()->user()->username,
            'title' => $listing->title,
            'business_type' => $listing->business_type,
            'sale_type' => $listing->sale_type,
            'asking_price' => $listing->asking_price,
            'status' => $listing->status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

            $notify[] = ['success', 'Listing updated successfully'];
            return redirect()->route('user.listing.index')->withNotify($notify);
        } catch (\Exception $e) {
            \Log::error('Listing update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'listing_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            $notify[] = ['error', 'An error occurred while updating your listing. Please try again.'];
            return back()->withInput($request->input())->withNotify($notify);
        }
    }

    public function show($id)
    {
        $pageTitle = 'Listing Details';
        $listing = Listing::where('user_id', auth()->id())
            ->with(['images', 'metrics', 'bids.user', 'offers.buyer', 'questions.asker', 'watchlist'])
            ->findOrFail($id);

        $stats = [
            'total_views' => $listing->view_count,
            'total_watchers' => $listing->watchlist_count,
            'total_bids' => $listing->total_bids,
            'total_offers' => $listing->offers()->count(),
            'total_questions' => $listing->questions()->count(),
        ];

        $viewsLast7 = (int) ListingView::where('listing_id', $listing->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $viewsPrevious7 = (int) ListingView::where('listing_id', $listing->id)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->count();
        $viewsByDay = ListingView::where('listing_id', $listing->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('views', 'date')
            ->toArray();

        $featuredFeePerDay = (float) MarketplaceSetting::getValue('featured_listing_fee', 0);

        return view('Template::user.listing.show', compact('pageTitle', 'listing', 'stats', 'featuredFeePerDay', 'viewsLast7', 'viewsPrevious7', 'viewsByDay'));
    }

    /**
     * Feature an active listing for N days (charges featured_listing_fee per day)
     */
    public function feature(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'pay_via' => 'required|in:1,2', // 1 = wallet, 2 = pay via gateway
        ]);

        $userId = auth()->id();
        $days = (int) $request->days;
        $payVia = (int) $request->pay_via;

        $feePerDay = (float) MarketplaceSetting::getValue('featured_listing_fee', 0);
        if ($feePerDay < 0) {
            $feePerDay = 0;
        }

        if ($feePerDay == 0.0) {
            $notify[] = ['error', 'Featured listings are not available right now'];
            return back()->withNotify($notify);
        }

        $totalFee = $feePerDay * $days;

        // Pay via gateway: redirect to deposit checkout; fee is applied after payment success
        if ($payVia === 2) {
            $listing = Listing::where('user_id', $userId)
                ->where('status', Status::LISTING_ACTIVE)
                ->findOrFail($id);
            $checkout = [
                'type' => 'featured_listing_fee',
                'amount' => $totalFee,
                'listing_id' => (int) $listing->id,
                'days' => $days,
            ];
            session()->put('checkout', encrypt($checkout));
            $notify[] = ['info', 'Complete payment to feature your listing.'];
            return redirect()->route('user.deposit.index', ['type' => 'checkout'])->withNotify($notify);
        }

        DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->findOrFail($userId);
            $listing = Listing::lockForUpdate()
                ->where('user_id', $userId)
                ->where('status', Status::LISTING_ACTIVE)
                ->findOrFail($id);

            // Re-check balance inside lock (wallet payment)
            if ($totalFee > 0 && $user->balance < $totalFee) {
                DB::rollBack();
                $notify[] = ['error', 'Insufficient balance. Required: ' . showAmount($totalFee) . '. Current balance: ' . showAmount($user->balance) . '. You can pay via gateway instead.'];
                return back()->withNotify($notify);
            }

            // Deduct fee and create transaction
            if ($totalFee > 0) {
                $user->balance -= $totalFee;
                $user->save();

                $trx = getTrx();
                $transaction               = new Transaction();
                $transaction->user_id      = $user->id;
                $transaction->amount       = $totalFee;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->remark       = 'featured_listing_fee';
                $transaction->details      = 'Featured listing fee for listing: ' . $listing->listing_number;
                $transaction->trx          = $trx;
                $transaction->save();
            }

            // Extend from current featured_until if still active; otherwise from now
            $base = now();
            if ($listing->is_featured && $listing->featured_until && $listing->featured_until->isFuture()) {
                $base = $listing->featured_until;
            }

            $listing->is_featured = true;
            $listing->featured_until = $base->copy()->addDays($days);
            $listing->save();

            DB::commit();

            $notify[] = ['success', 'Listing featured until ' . showDateTime($listing->featured_until, 'd M, Y')];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Feature listing failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'listing_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            $notify[] = ['error', 'An error occurred while featuring your listing. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function cancel($id)
    {
        $listing = Listing::where('user_id', auth()->id())
            ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_ACTIVE])
            ->findOrFail($id);

        // Check if auction has bids
        if ($listing->sale_type === 'auction' && $listing->total_bids > 0) {
            $notify[] = ['error', 'Cannot cancel listing with active bids'];
            return back()->withNotify($notify);
        }

        $listing->status = Status::LISTING_CANCELLED;
        $listing->save();

        $notify[] = ['success', 'Listing cancelled successfully'];
        return back()->withNotify($notify);
    }

    /**
     * AJAX endpoint: quote the Direct payout listing fee for the current pricing inputs.
     */
    public function directPayoutFeeQuote(Request $request)
    {
        $request->validate([
            'sale_type' => 'required|in:fixed_price,auction',
            'asking_price' => 'nullable|numeric|min:0',
            'starting_bid' => 'nullable|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'buy_now_price' => 'nullable|numeric|min:0',
        ]);

        $saleType = $request->sale_type;
        if ($saleType === 'fixed_price') {
            $baseAmount = (float) ($request->asking_price ?? 0);
        } else {
            $baseAmount = (float) ($request->buy_now_price ?: ($request->reserve_price ?: $request->starting_bid));
        }

        $baseAmount = (float) max(0, $baseAmount);
        $breakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees($baseAmount, 'direct_payout_listing_fee', 'system');
        $fee = (float) ($breakdown['seller'] ?? $breakdown['total'] ?? 0);

        $user = auth()->user();
        $balance = (float) ($user->balance ?? 0);

        return response()->json([
            'base_amount' => $baseAmount,
            'fee' => $fee,
            'balance' => $balance,
            'can_afford' => $balance >= $fee,
            'used' => $breakdown['used'] ?? null,
            'items' => $breakdown['fees'] ?? [],
        ]);
    }

    /**
     * Upload a single image to an existing listing (AJAX). Saves immediately so seller can add images before clicking Update.
     */
    public function uploadImage(Request $request, $id)
    {
        $listing = Listing::where('user_id', auth()->id())
            ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_REJECTED, Status::LISTING_ACTIVE])
            ->findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'image.image' => 'The file must be an image (JPEG, PNG, JPG, or GIF).',
            'image.mimes' => 'The image must be a file of type: JPEG, PNG, JPG, or GIF.',
            'image.max' => 'The image must not be larger than 2MB.',
        ]);

        $path = getFilePath('listing');
        $size = getFileSize('listing');
        $filename = fileUploader($request->file('image'), $path, $size);
        $sortOrder = (int) ($listing->images()->max('sort_order') ?? -1) + 1;
        $isPrimary = $listing->images()->count() === 0;

        $image = ListingImage::create([
            'listing_id' => $listing->id,
            'image' => $filename,
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
        ]);

        if ($isPrimary) {
            ListingImage::where('listing_id', $listing->id)->where('id', '!=', $image->id)->update(['is_primary' => false]);
        }

        $imageUrl = getImage($path . '/' . $filename);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded',
            'image' => [
                'id' => $image->id,
                'url' => $imageUrl,
                'is_primary' => $image->is_primary,
            ],
        ]);
    }

    public function deleteImage($id)
    {
        $image = ListingImage::whereHas('listing', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        // Delete file
        $path = getFilePath('listing') . '/' . $image->image;
        if (file_exists($path)) {
            unlink($path);
        }

        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image deleted']);
    }

    public function setPrimaryImage($id)
    {
        $image = ListingImage::whereHas('listing', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        // Remove primary from other images
        ListingImage::where('listing_id', $image->listing_id)
            ->where('id', '!=', $id)
            ->update(['is_primary' => false]);

        $image->is_primary = true;
        $image->save();

        return response()->json(['success' => true, 'message' => 'Primary image set']);
    }

    public function addMetrics(Request $request, $id)
    {
        $listing = Listing::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'period_date' => 'required|date',
            'period_type' => 'required|in:monthly,weekly,daily',
            'revenue' => 'nullable|numeric|min:0',
            'expenses' => 'nullable|numeric|min:0',
            'visitors' => 'nullable|integer|min:0',
            'page_views' => 'nullable|integer|min:0',
        ]);

        ListingMetric::updateOrCreate(
            [
                'listing_id' => $listing->id,
                'period_date' => $request->period_date,
                'period_type' => $request->period_type,
            ],
            [
                'revenue' => $request->revenue ?? 0,
                'expenses' => $request->expenses ?? 0,
                'profit' => ($request->revenue ?? 0) - ($request->expenses ?? 0),
                'visitors' => $request->visitors ?? 0,
                'page_views' => $request->page_views ?? 0,
                'unique_visitors' => $request->unique_visitors ?? 0,
                'followers' => $request->followers ?? 0,
                'subscribers' => $request->subscribers ?? 0,
                'downloads' => $request->downloads ?? 0,
                'email_subscribers' => $request->email_subscribers ?? 0,
                'notes' => $request->notes,
            ]
        );

        $notify[] = ['success', 'Metrics added successfully'];
        return back()->withNotify($notify);
    }

    // Answer a question on the listing
    public function answerQuestion(Request $request, $id)
    {
        $listing = Listing::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'question_id' => 'required|exists:listing_questions,id',
            'answer' => 'required|string|max:2000',
        ]);

        $question = $listing->questions()->findOrFail($request->question_id);
        $question->answer = $request->answer;
        $question->answered_at = now();
        $question->status = Status::QUESTION_ANSWERED;
        $question->save();

        // Notify the user who asked the question
        if ($question->user) {
            notify($question->user, 'QUESTION_ANSWERED', [
                'listing_title' => $listing->title,
                'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                'question' => Str::limit($question->question, 100),
                'answer' => Str::limit($question->answer, 200),
                'seller_username' => $listing->seller->username ?? $listing->seller->name ?? 'Seller',
            ]);
        }

        $notify[] = ['success', 'Question answered successfully'];
        return back()->withNotify($notify);
    }

    private function fillBusinessTypeFields($listing, $request)
    {
        switch ($request->business_type) {
            case 'domain':
                // Extract clean domain name using helper
                $domainName = extractDomain($request->domain_name);
                
                if (!$domainName) {
                    // Fallback to manual extraction
                    $domainName = $request->domain_name;
                    if (preg_match('/^https?:\/\/(.+)$/i', $domainName, $matches)) {
                        $domainName = $matches[1];
                    }
                    $domainName = preg_replace('/^www\./i', '', $domainName);
                    $domainName = explode('/', $domainName)[0];
                }
                
                $listing->domain_name = $domainName;
                $listing->domain_extension = $request->domain_extension;
                $listing->domain_registrar = $request->domain_registrar;
                $listing->domain_expiry = $request->domain_expiry;
                $listing->domain_age_years = $request->domain_age_years ?? 0;
                // Set URL from domain name for verification purposes (normalized)
                $listing->url = normalizeUrl($request->domain_name);
                break;

            case 'website':
                // Normalize website URL
                $listing->url = normalizeUrl($request->website_url);
                $listing->niche = $request->website_niche ?? $request->niche ?? null;
                $listing->tech_stack = $request->website_tech_stack ?? $request->tech_stack ?? null;
                $listing->domain_registrar = $request->website_domain_registrar ?? $request->domain_registrar ?? null;
                $listing->domain_expiry = $request->website_domain_expiry ?? $request->domain_expiry ?? null;
                $listing->business_location = $request->business_location ?? null;
                $listing->overall_churn_percent = $request->overall_churn_percent ?? null;
                $listing->site_age_months = $request->site_age_months ?? null;
                // Also store domain name for easier searching
                $listing->domain_name = extractDomain($request->website_url);
                break;

            case 'social_media_account':
                $listing->platform = $request->platform;
                $listing->niche = $request->social_niche ?? $request->niche ?? null;
                $listing->url = $request->social_url;
                $listing->followers_count = $request->followers_count ?? 0;
                $listing->subscribers_count = $request->subscribers_count ?? 0;
                $listing->engagement_rate = $request->engagement_rate ?? 0;
                
                // Clear OAuth account data from session after successful listing creation
                if ($request->oauth_connected) {
                    session()->forget('oauth_account_data');
                }
                break;

            case 'mobile_app':
                $listing->app_store_url = $request->app_store_url;
                $listing->play_store_url = $request->play_store_url;
                $listing->downloads_count = $request->downloads_count ?? 0;
                $listing->app_rating = $request->app_rating ?? 0;
                $listing->tech_stack = $request->mobile_tech_stack ?? $request->tech_stack ?? null;
                break;

            case 'desktop_app':
                $listing->url = $request->desktop_url;
                $listing->downloads_count = $request->downloads_count ?? 0;
                $listing->tech_stack = $request->desktop_tech_stack ?? $request->tech_stack ?? null;
                break;
        }

        // Normalize monetization fields for supported types (website/apps/social).
        if (in_array($request->business_type, ['website', 'social_media_account', 'mobile_app', 'desktop_app'], true)) {
            $methods = $request->monetization_methods;
            if (!is_array($methods)) {
                $methods = $methods ? [$methods] : [];
            }
            $methods = array_values(array_unique(array_filter($methods, fn ($v) => is_string($v) && trim($v) !== '')));
            $listing->monetization_methods = $methods ?: null;
            $listing->monetization_other = $request->monetization_other ?: null;
        } else {
            $listing->monetization_methods = null;
            $listing->monetization_other = null;
        }
    }

    /**
     * Upload listing images. primaryIndex (0-based) indicates which uploaded image is the primary.
     */
    private function uploadImages($listing, array $files, $primaryIndex = 0)
    {
        $path = getFilePath('listing');
        $size = getFileSize('listing');
        $existingCount = $listing->images()->count();
        $sortStart = (int) ($listing->images()->max('sort_order') ?? -1);
        $primaryIndex = max(0, min($primaryIndex, count($files) - 1));
        $createdIdsByIndex = [];

        foreach ($files as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $filename = fileUploader($file, $path, $size);
            $sortOrder = $sortStart + 1 + $index;
            $img = ListingImage::create([
                'listing_id' => $listing->id,
                'image' => $filename,
                'is_primary' => false,
                'sort_order' => $sortOrder,
            ]);
            $createdIdsByIndex[$index] = $img->id;
        }

        $primaryId = $createdIdsByIndex[$primaryIndex] ?? reset($createdIdsByIndex);
        if ($primaryId && $existingCount === 0) {
            ListingImage::where('listing_id', $listing->id)->where('id', '!=', $primaryId)->update(['is_primary' => false]);
            ListingImage::where('listing_id', $listing->id)->where('id', $primaryId)->update(['is_primary' => true]);
        }
    }

    private function getBusinessTypes()
    {
        $allTypes = [
            'domain' => 'Domain Name',
            'website' => 'Website',
            'social_media_account' => 'Social Media Account',
            'mobile_app' => 'Mobile App',
            'desktop_app' => 'Desktop App',
        ];

        // Filter by marketplace settings
        $allowedTypes = [];
        foreach ($allTypes as $key => $label) {
            if (MarketplaceSetting::allowBusinessType($key)) {
                $allowedTypes[$key] = $label;
            }
        }

        return $allowedTypes;
    }

    private function getPlatforms()
    {
        return [
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'twitter' => 'Twitter/X',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'snapchat' => 'Snapchat',
            'twitch' => 'Twitch',
        ];
    }

    /**
     * Get platforms that support OAuth login
     */
    private function getOAuthSupportedPlatforms()
    {
        return ['instagram', 'facebook', 'twitter', 'linkedin'];
    }

    /**
     * Check if platform supports OAuth
     */
    private function platformSupportsOAuth($platform)
    {
        return in_array(strtolower($platform), $this->getOAuthSupportedPlatforms());
    }

    /**
     * Redirect to OAuth for account data fetching
     */
    public function redirectToAccountOAuth($platform)
    {
        try {
            if (!$this->platformSupportsOAuth($platform)) {
                $notify[] = ['error', 'OAuth is not supported for this platform'];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }

            // Map platform to socialite provider
            $platformMap = [
                'instagram' => 'instagram',
                'facebook' => 'facebook',
                'twitter' => 'twitter',
                'linkedin' => 'linkedin',
            ];

            $provider = $platformMap[strtolower($platform)] ?? strtolower($platform);

            $user = auth()->user();
            
            // IMPORTANT: Store context in BOTH session AND cache
            // Cache is more reliable across OAuth redirects than session alone
            $cacheKey = 'oauth_account_data_' . $user->id . '_' . $platform;
            $cacheData = [
                'platform' => $platform,
                'purpose' => 'listing_creation',
                'user_id' => $user->id,
                'timestamp' => now()->timestamp,
            ];
            
            // Store in cache (5 minutes TTL) - more reliable than session across redirects
            \Illuminate\Support\Facades\Cache::put($cacheKey, $cacheData, 300);
            
            // Also store in session as backup
            session()->put('account_data_fetch_context', $cacheData);
            session()->put('oauth_for_account_data', true);
            session()->put('oauth_account_data_cache_key', $cacheKey);
            
            // Ensure session is saved before redirect
            session()->save();

            \Log::info('Initiating OAuth for account data fetch', [
                'platform' => $platform,
                'provider' => $provider,
                'user_id' => $user->id,
                'cache_key' => $cacheKey,
            ]);

            // Configure OAuth
            $socialLogin = new \App\Lib\SocialLogin($provider);
            return $socialLogin->redirectDriver();

        } catch (\Exception $e) {
            \Log::error('OAuth redirect error for account data fetch', [
                'platform' => $platform,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->forget('oauth_for_account_data');
            session()->forget('account_data_fetch_context');
            session()->forget('oauth_account_data_cache_key');
            
            // Clear any cache entries
            if (isset($cacheKey) && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }

            $notify[] = ['error', 'Failed to initiate OAuth login: ' . $e->getMessage()];
            return redirect()->route('user.listing.create')->withNotify($notify);
        }
    }

    /**
     * Handle OAuth callback for account data fetching
     */
    public function handleAccountOAuthCallback($platform, $provider = null)
    {
        try {
            $userId = auth()->id();
            $cacheKey = 'oauth_account_data_' . $userId . '_' . $platform;
            
            \Log::info('Account data fetch callback received', [
                'platform' => $platform,
                'provider' => $provider,
                'has_session_flag' => session()->has('oauth_for_account_data'),
                'has_cache_flag' => \Illuminate\Support\Facades\Cache::has($cacheKey),
                'user_id' => $userId,
            ]);

            // Check if this OAuth is for account data fetch (check cache first, then session)
            $context = null;
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $context = \Illuminate\Support\Facades\Cache::get($cacheKey);
                \Log::info('Found account data fetch context in cache');
            } elseif (session()->has('oauth_for_account_data')) {
                $context = session()->get('account_data_fetch_context');
                \Log::info('Found account data fetch context in session');
            }
            
            if (!$context) {
                \Log::warning('OAuth callback received but context missing from both cache and session', [
                    'platform' => $platform,
                    'provider' => $provider,
                    'user_id' => $userId,
                ]);
                $notify[] = ['error', 'OAuth session expired. Please try again.'];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }

            // Map platform to provider if not provided
            if (!$provider) {
                $platformMap = [
                    'instagram' => 'instagram',
                    'facebook' => 'facebook',
                    'twitter' => 'twitter',
                    'linkedin' => 'linkedin-openid',
                    'youtube' => 'google',
                ];
                $provider = $platformMap[strtolower($platform)] ?? strtolower($platform);
            }

            // Get OAuth user - DO NOT LOG THEM IN, just get their data
            $driver = \Laravel\Socialite\Facades\Socialite::driver($provider);
            $oauthUser = $driver->user();

            // Handle LinkedIn OpenID Connect
            if ($provider === 'linkedin-openid' && isset($oauthUser->sub)) {
                $oauthUser->id = $oauthUser->sub;
            }

            // Extract account data
            $username = $oauthUser->nickname ?? $oauthUser->name ?? null;
            
            // For LinkedIn, try to get username from email or other fields
            if ($platform === 'linkedin' && !$username) {
                $username = $oauthUser->email ?? $oauthUser->id ?? null;
            }
            
            // Build account URL based on platform
            $accountUrl = $this->buildAccountUrl($platform, $username);

            \Log::info('Account data fetched successfully', [
                'platform' => $platform,
                'username' => $username,
                'oauth_id' => $oauthUser->id,
            ]);

            // Store account data in session to auto-fill form
            session()->put('oauth_account_data', [
                'platform' => $platform,
                'username' => $username,
                'account_url' => $accountUrl,
                'oauth_id' => $oauthUser->id,
                'connected' => true,
            ]);

            // Clear OAuth session flags and cache (but keep oauth_account_data for form population)
            session()->forget('oauth_for_account_data');
            session()->forget('account_data_fetch_context');
            session()->forget('oauth_account_data_cache_key');
            
            // Clear cache
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }
            
            // Ensure session is saved
            session()->save();

            $notify[] = ['success', 'Account connected successfully! Your account details have been filled in. You can now continue to the next step.'];
            return redirect()->route('user.listing.create')->withNotify($notify);

        } catch (\Exception $e) {
            \Log::error('OAuth callback error for account data fetch', [
                'platform' => $platform,
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->forget('oauth_for_account_data');
            session()->forget('account_data_fetch_context');
            session()->forget('oauth_account_data_cache_key');
            
            // Clear cache
            if (isset($cacheKey) && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }

            $notify[] = ['error', 'Failed to fetch account data: ' . $e->getMessage()];
            return redirect()->route('user.listing.create')->withNotify($notify);
        }
    }

    /**
     * Build account URL based on platform and username
     */
    private function buildAccountUrl($platform, $username)
    {
        if (!$username) {
            return null;
        }

        $urls = [
            'instagram' => 'https://instagram.com/' . $username,
            'facebook' => 'https://facebook.com/' . $username,
            'twitter' => 'https://twitter.com/' . $username,
            'linkedin' => 'https://linkedin.com/in/' . $username,
        ];

        return $urls[strtolower($platform)] ?? null;
    }

    /**
     * Sanitize listing input data
     */
    private function sanitizeListingInput(Request $request)
    {
        // Sanitize text inputs
        $textFields = ['title', 'tagline', 'description', 'confidential_reason'];
        foreach ($textFields as $field) {
            if ($request->has($field) && $request->$field) {
                // Remove potentially harmful HTML/script content
                $request->merge([$field => strip_tags($request->$field)]);
                // Trim whitespace
                $request->merge([$field => trim($request->$field)]);
            }
        }

        // Sanitize URLs
        if ($request->has('domain_name') && $request->domain_name) {
            $request->merge(['domain_name' => filter_var($request->domain_name, FILTER_SANITIZE_URL)]);
        }
        if ($request->has('website_url') && $request->website_url) {
            $request->merge(['website_url' => filter_var($request->website_url, FILTER_SANITIZE_URL)]);
        }

        // Ensure numeric fields are properly formatted
        $numericFields = ['asking_price', 'starting_bid', 'reserve_price', 'buy_now_price',
                         'bid_increment', 'monthly_revenue', 'monthly_profit', 'yearly_revenue',
                         'yearly_profit', 'monthly_visitors', 'yearly_visitors'];

        foreach ($numericFields as $field) {
            if ($request->has($field) && $request->$field !== null) {
                $value = floatval($request->$field);
                if ($value < 0) $value = 0;
                $request->merge([$field => $value]);
            }
        }
    }

    /**
     * Validate listing business logic
     */
    private function validateListingBusinessLogic(Request $request, $user)
    {
        $saleType = $request->sale_type;

        // Auction-specific validations
        if ($saleType === 'auction') {
            // Reserve price cannot be lower than starting bid
            if ($request->reserve_price > 0 && $request->reserve_price <= $request->starting_bid) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reserve_price' => ['Reserve price must be higher than the starting bid']
                ]);
            }

            // Buy now price must be reasonable
            if ($request->buy_now_price > 0 && $request->buy_now_price <= $request->starting_bid) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'buy_now_price' => ['Buy now price must be higher than the starting bid']
                ]);
            }

            // Bid increment validation
            if ($request->bid_increment > $request->starting_bid * 0.5) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'bid_increment' => ['Bid increment cannot be more than 50% of the starting bid']
                ]);
            }
        }

        // Financial validation - profit cannot exceed revenue (skip for domain)
        if ($request->business_type !== 'domain') {
            if ($request->monthly_profit > $request->monthly_revenue) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'monthly_profit' => ['Monthly profit cannot exceed monthly revenue']
                ]);
            }

            if ($request->yearly_profit > $request->yearly_revenue) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'yearly_profit' => ['Yearly profit cannot exceed yearly revenue']
                ]);
            }
        }

        // Domain/website validation
        if ($request->business_type === 'domain' && $request->domain_name) {
            // Check for suspicious domains
            $suspiciousPatterns = ['/localhost/i', '/127\.0\.0\.1/i', '/\.local/i'];
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $request->domain_name)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'domain_name' => ['Invalid domain name']
                    ]);
                }
            }
            $domainHost = extractDomain($request->domain_name);
            if ($domainHost && $domainHost === platform_domain()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'domain_name' => [__('general.platform_domain_blocked')]
                ]);
            }
        }

        // User status validation
        if ($user->status !== \App\Constants\Status::USER_ACTIVE) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user' => ['Your account must be active to create listings']
            ]);
        }
    }

    /**
     * Extract domain from URL
     */
    private function extractDomain($url)
    {
        if (!$url) return null;

        // Remove protocol
        $url = preg_replace('#^https?://#', '', $url);

        // Remove www
        $url = preg_replace('#^www\.#', '', $url);

        // Remove path and query
        $domain = parse_url('https://' . $url, PHP_URL_HOST);

        return $domain ?: null;
    }
}

