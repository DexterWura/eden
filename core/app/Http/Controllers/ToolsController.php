<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MarketplaceFee;
use App\Models\MarketplaceSetting;
use App\Models\EscrowCharge;
use App\Models\Listing;
use App\Models\ListingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ToolsController extends Controller
{
    /**
     * Tools index page - list all available tools
     */
    public function index()
    {
        $pageTitle = 'Free Tools';
        
        $tools = [
            [
                'name' => 'Business Valuation Calculator',
                'description' => 'Get an instant estimate of your online business value based on industry multiples.',
                'icon' => 'las la-calculator',
                'route' => 'tools.valuation',
                'color' => 'primary'
            ],
            [
                'name' => 'Seller Earnings Calculator',
                'description' => 'Calculate your net proceeds after platform fees, escrow costs, and payment processing.',
                'icon' => 'las la-coins',
                'route' => 'tools.seller-earnings',
                'color' => 'success'
            ],
            [
                'name' => 'Listing Comparison Tool',
                'description' => 'Compare up to 3 listings side-by-side to find the best investment opportunity.',
                'icon' => 'las la-balance-scale',
                'route' => 'tools.compare',
                'color' => 'info'
            ],
            [
                'name' => 'Buyer Payback Calculator',
                'description' => 'See how long it takes to get your money back. Use "Determine payback" on any listing page.',
                'icon' => 'las la-clock',
                'route' => 'tools.payback',
                'color' => 'warning'
            ],
        ];
        
        return view('Template::tools.index', compact('pageTitle', 'tools'));
    }

    /**
     * Buyer Payback Calculator - landing page with search and filters; click a listing to see payback on its detail page
     */
    public function payback(Request $request)
    {
        $pageTitle = __('Buyer Payback Calculator');

        $listings = Listing::active()
            ->with(['primaryImage', 'seller:id,username,firstname,lastname', 'listingCategory:id,name,slug'])
            ->where(function ($q) {
                $q->where('is_confidential', false);
                if (auth()->check()) {
                    $q->orWhere(function ($confidentialQ) {
                        $confidentialQ->where('is_confidential', true)
                            ->where(function ($accessQ) {
                                $accessQ->where('user_id', auth()->id())
                                    ->orWhereHas('signedNdas', function ($ndaQ) {
                                        $ndaQ->where('user_id', auth()->id());
                                    });
                            });
                    });
                }
            })
            ->when($request->search, function ($q, $search) {
                return $q->search($search);
            })
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->category, function ($q, $categoryId) {
                return $q->where('listing_category_id', $categoryId);
            })
            ->when($request->min_price, function ($q, $min) {
                return $q->where(function ($query) use ($min) {
                    $query->where(function ($q) use ($min) {
                        $q->where('sale_type', 'fixed_price')->where('asking_price', '>=', $min);
                    })->orWhere(function ($q) use ($min) {
                        $q->where('sale_type', 'auction')
                            ->where(function ($subQ) use ($min) {
                                $subQ->where('current_bid', '>=', $min)->orWhere('starting_bid', '>=', $min);
                            });
                    });
                });
            })
            ->when($request->max_price, function ($q, $max) {
                return $q->where(function ($query) use ($max) {
                    $query->where(function ($q) use ($max) {
                        $q->where('sale_type', 'fixed_price')->where('asking_price', '<=', $max);
                    })->orWhere(function ($q) use ($max) {
                        $q->where('sale_type', 'auction')
                            ->where(function ($subQ) use ($max) {
                                $subQ->where('current_bid', '<=', $max)->orWhere('starting_bid', '<=', $max);
                            });
                    });
                });
            })
            ->when($request->min_revenue, function ($q, $min) {
                return $q->where('monthly_revenue', '>=', $min);
            })
            ->when($request->max_revenue, function ($q, $max) {
                return $q->where('monthly_revenue', '<=', $max);
            })
            ->when($request->sort, function ($q, $sort) {
                switch ($sort) {
                    case 'price_low':
                        return $q->orderByRaw('COALESCE(NULLIF(current_bid, 0), asking_price) ASC');
                    case 'price_high':
                        return $q->orderByRaw('COALESCE(NULLIF(current_bid, 0), asking_price) DESC');
                    case 'revenue_high':
                        return $q->orderBy('monthly_revenue', 'desc');
                    case 'revenue_low':
                        return $q->orderBy('monthly_revenue', 'asc');
                    case 'newest':
                    default:
                        return $q->orderBy('approved_at', 'desc');
                }
            }, function ($q) {
                return $q->orderBy('approved_at', 'desc');
            })
            ->paginate(getPaginate());

        $categories = Cache::remember('marketplace_categories', 3600, function () {
            return ListingCategory::active()->orderBy('sort_order')->get();
        });

        $businessTypes = [
            'domain' => __('Domain Names'),
            'website' => __('Websites'),
            'social_media_account' => __('Social Media Accounts'),
            'mobile_app' => __('Mobile Apps'),
            'desktop_app' => __('Desktop Apps'),
        ];

        return view('Template::tools.payback', compact('pageTitle', 'listings', 'categories', 'businessTypes'));
    }

    /**
     * Business Valuation Calculator
     */
    public function valuation()
    {
        $pageTitle = 'How Much Is Your Online Business Worth?';
        
        // Business types with their base multiples for valuation
        $businessTypes = [
            'saas' => [
                'name' => 'SaaS / Software',
                'baseMultiple' => 4.0,
                'maxMultiple' => 6.0,
                'icon' => 'las la-cloud'
            ],
            'ecommerce' => [
                'name' => 'E-commerce Store',
                'baseMultiple' => 2.5,
                'maxMultiple' => 4.0,
                'icon' => 'las la-shopping-cart'
            ],
            'content' => [
                'name' => 'Blog / Content Site',
                'baseMultiple' => 3.0,
                'maxMultiple' => 4.5,
                'icon' => 'las la-blog'
            ],
            'marketplace' => [
                'name' => 'Marketplace',
                'baseMultiple' => 4.0,
                'maxMultiple' => 6.0,
                'icon' => 'las la-store'
            ],
            'app' => [
                'name' => 'Mobile App',
                'baseMultiple' => 3.0,
                'maxMultiple' => 5.0,
                'icon' => 'las la-mobile'
            ],
            'service' => [
                'name' => 'Service Business',
                'baseMultiple' => 2.5,
                'maxMultiple' => 4.0,
                'icon' => 'las la-concierge-bell'
            ],
            'other' => [
                'name' => 'Other',
                'baseMultiple' => 2.5,
                'maxMultiple' => 4.0,
                'icon' => 'las la-globe'
            ],
        ];

        $ageOptions = [
            '0' => 'Less than 1 year',
            '1' => '1-2 years',
            '2' => '2-3 years',
            '3' => '3+ years',
        ];

        // Get categories for the marketplace link
        $categories = Category::active()->get();

        return view('Template::tools.valuation', compact(
            'pageTitle',
            'businessTypes',
            'ageOptions',
            'categories'
        ));
    }

    /**
     * Seller Earnings Calculator
     * Pulls actual fees from database configuration
     * Supports both System Wallet and Direct Payout methods
     */
    public function sellerEarnings()
    {
        $pageTitle = 'Seller Earnings Calculator';
        $general = gs();
        
        // Get marketplace fees from database
        $escrowServiceFees = MarketplaceFee::where('context', 'escrow_service_fee')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        $directPayoutFees = MarketplaceFee::where('context', 'direct_payout_listing_fee')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // ==========================================
        // SYSTEM WALLET PAYOUT FEES
        // - Seller receives money in platform wallet
        // - Escrow service fees apply (paid by buyer/seller as configured)
        // - No upfront listing fees
        // ==========================================
        $systemWalletFees = [
            'buyer' => [],
            'seller' => [],
        ];
        
        if ($escrowServiceFees->count() > 0) {
            foreach ($escrowServiceFees as $fee) {
                $feeData = [
                    'name' => $fee->name,
                    'percent' => (float) $fee->percent,
                    'fixed' => (float) $fee->fixed,
                    'cap' => (float) $fee->cap,
                    'payer' => $fee->payer,
                    'context' => 'escrow_service_fee',
                    'description' => $fee->payer === 'buyer' ? 'Paid by buyer for escrow protection' : 'Platform fee on successful sale',
                    'icon' => 'las la-shield-alt'
                ];
                
                if ($fee->payer === 'buyer') {
                    $systemWalletFees['buyer'][] = $feeData;
                } else {
                    $systemWalletFees['seller'][] = $feeData;
                }
            }
            $systemFeeSource = 'marketplace_fees';
        } else {
            // Fall back to legacy global settings
            $percentCharge = (float) ($general->percent_charge ?? 0);
            $fixedCharge = (float) ($general->fixed_charge ?? 0);
            $chargeCap = (float) ($general->charge_cap ?? 0);
            
            $systemWalletFees['buyer'][] = [
                'name' => 'Escrow Service Fee',
                'percent' => $percentCharge,
                'fixed' => $fixedCharge,
                'cap' => $chargeCap,
                'payer' => 'buyer',
                'context' => 'escrow_service_fee',
                'description' => 'Platform escrow protection fee',
                'icon' => 'las la-shield-alt'
            ];
            $systemFeeSource = 'legacy';
        }

        // ==========================================
        // DIRECT PAYOUT FEES
        // - Seller receives money outside platform (bank, PayPal, etc.)
        // - Seller pays upfront listing fee
        // - NO escrow service fees (buyer pays seller directly)
        // ==========================================
        $directPayoutFeesData = [
            'buyer' => [], // No buyer fees for direct payout
            'seller' => [],
        ];
        
        if ($directPayoutFees->count() > 0) {
            foreach ($directPayoutFees as $fee) {
                $directPayoutFeesData['seller'][] = [
                    'name' => $fee->name,
                    'percent' => (float) $fee->percent,
                    'fixed' => (float) $fee->fixed,
                    'cap' => (float) $fee->cap,
                    'payer' => 'seller',
                    'context' => 'direct_payout_listing_fee',
                    'description' => 'Upfront fee for receiving payments outside the platform',
                    'icon' => 'las la-external-link-alt'
                ];
            }
            $directFeeSource = 'marketplace_fees';
        } else {
            // Fall back to legacy settings for direct payout
            $percentCharge = (float) ($general->percent_charge ?? 0);
            $fixedCharge = (float) ($general->fixed_charge ?? 0);
            $chargeCap = (float) ($general->charge_cap ?? 0);
            
            if ($percentCharge > 0 || $fixedCharge > 0) {
                $directPayoutFeesData['seller'][] = [
                    'name' => 'Direct Payout Listing Fee',
                    'percent' => $percentCharge,
                    'fixed' => $fixedCharge,
                    'cap' => $chargeCap,
                    'payer' => 'seller',
                    'context' => 'direct_payout_listing_fee',
                    'description' => 'Upfront fee for receiving payments outside the platform',
                    'icon' => 'las la-external-link-alt'
                ];
            }
            $directFeeSource = 'legacy';
        }
        
        // Get tiered escrow charges (for legacy system)
        $tieredCharges = EscrowCharge::orderBy('minimum')->get();

        // Sale price tiers for quick selection
        $priceTiers = [
            5000, 10000, 25000, 50000, 100000, 250000, 500000, 1000000
        ];

        return view('Template::tools.seller-earnings', compact(
            'pageTitle',
            'systemWalletFees',
            'directPayoutFeesData',
            'tieredCharges',
            'systemFeeSource',
            'directFeeSource',
            'priceTiers'
        ));
    }

    /**
     * Listing Comparison Tool - Compare up to 3 listings side by side
     */
    public function listingCompare(Request $request)
    {
        $pageTitle = 'Compare Listings';
        
        // Get categories for filtering
        $categories = ListingCategory::orderBy('name')->get();
        
        // Business types
        $businessTypes = [
            'website' => 'Website',
            'domain' => 'Domain',
            'mobile_app' => 'Mobile App',
            'desktop_app' => 'Desktop App',
            'social_media_account' => 'Social Media Account',
        ];
        
        // Pre-load listings if IDs are passed
        $preloadedListings = [];
        $listingIds = $request->get('listings', '');
        if ($listingIds) {
            $ids = array_filter(explode(',', $listingIds));
            if (count($ids) > 0) {
                $preloadedListings = Listing::active()
                    ->whereIn('id', array_slice($ids, 0, 3))
                    ->with(['seller:id,username,firstname,lastname', 'listingCategory:id,name', 'primaryImage'])
                    ->get()
                    ->map(fn($l) => $this->formatListingForComparison($l));
            }
        }
        
        // Get featured/popular listings for suggestions
        $suggestedListings = Listing::active()
            ->with(['seller:id,username', 'listingCategory:id,name', 'primaryImage'])
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->limit(6)
            ->get()
            ->map(fn($l) => $this->formatListingForComparison($l));

        return view('Template::tools.compare', compact(
            'pageTitle',
            'categories',
            'businessTypes',
            'preloadedListings',
            'suggestedListings'
        ));
    }

    /**
     * AJAX search for listings to compare
     */
    public function compareSearch(Request $request)
    {
        $query = Listing::active()
            ->with(['seller:id,username', 'listingCategory:id,name', 'primaryImage']);
        
        // Search by keyword
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('listing_number', 'like', "%{$search}%")
                  ->orWhere('domain_name', 'like', "%{$search}%")
                  ->orWhere('niche', 'like', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('listing_category_id', $request->category);
        }
        
        // Filter by business type
        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }
        
        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where(function($q) use ($request) {
                $q->where('asking_price', '>=', $request->min_price)
                  ->orWhere('starting_bid', '>=', $request->min_price);
            });
        }
        
        if ($request->filled('max_price')) {
            $query->where(function($q) use ($request) {
                $q->where('asking_price', '<=', $request->max_price)
                  ->orWhere('starting_bid', '<=', $request->max_price);
            });
        }
        
        // Exclude already selected listings
        if ($request->filled('exclude')) {
            $excludeIds = array_filter(explode(',', $request->exclude));
            if (count($excludeIds) > 0) {
                $query->whereNotIn('id', $excludeIds);
            }
        }
        
        $listings = $query->orderByDesc('view_count')
            ->limit(20)
            ->get()
            ->map(fn($l) => $this->formatListingForComparison($l));

        return response()->json([
            'success' => true,
            'listings' => $listings
        ]);
    }

    /**
     * Get single listing data for comparison
     */
    public function compareListing($id)
    {
        $listing = Listing::active()
            ->with(['seller:id,username,firstname,lastname', 'listingCategory:id,name', 'primaryImage'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'listing' => $this->formatListingForComparison($listing)
        ]);
    }

    /**
     * Format listing data for comparison
     */
    private function formatListingForComparison(Listing $listing): array
    {
        $price = $listing->sale_type === 'auction' 
            ? ($listing->current_bid > 0 ? $listing->current_bid : $listing->starting_bid)
            : $listing->asking_price;
        
        $monthlyRevenue = (float) $listing->monthly_revenue;
        $monthlyProfit = (float) $listing->monthly_profit;
        $yearlyProfit = $monthlyProfit * 12;
        
        // Calculate multiples
        $revenueMultiple = $monthlyRevenue > 0 ? round($price / ($monthlyRevenue * 12), 2) : null;
        $profitMultiple = $monthlyProfit > 0 ? round($price / $yearlyProfit, 2) : null;
        $profitMargin = $monthlyRevenue > 0 ? round(($monthlyProfit / $monthlyRevenue) * 100, 1) : null;
        
        // Calculate value score (0-100)
        $valueScore = $this->calculateValueScore($listing, $price);
        
        return [
            'id' => $listing->id,
            'listing_number' => $listing->listing_number,
            'title' => $listing->title,
            'slug' => $listing->slug,
            'tagline' => $listing->tagline,
            'business_type' => $listing->business_type,
            'business_type_label' => ucwords(str_replace('_', ' ', $listing->business_type)),
            'sale_type' => $listing->sale_type,
            'sale_type_label' => $listing->sale_type === 'auction' ? 'Auction' : 'Fixed Price',
            'category' => $listing->listingCategory->name ?? 'Uncategorized',
            'seller' => [
                'username' => $listing->seller->username ?? 'Unknown',
                'name' => trim(($listing->seller->firstname ?? '') . ' ' . ($listing->seller->lastname ?? ''))
            ],
            'image' => $listing->primaryImage 
                ? asset('assets/images/listing/' . $listing->primaryImage->image_path) 
                : asset('assets/images/placeholder.png'),
            'url' => route('marketplace.listing.show', $listing->slug),
            
            // Pricing
            'price' => $price,
            'asking_price' => (float) $listing->asking_price,
            'starting_bid' => (float) $listing->starting_bid,
            'current_bid' => (float) $listing->current_bid,
            'buy_now_price' => (float) $listing->buy_now_price,
            
            // Financials
            'monthly_revenue' => $monthlyRevenue,
            'monthly_profit' => $monthlyProfit,
            'yearly_revenue' => $monthlyRevenue * 12,
            'yearly_profit' => $yearlyProfit,
            'profit_margin' => $profitMargin,
            'revenue_multiple' => $revenueMultiple,
            'profit_multiple' => $profitMultiple,
            
            // Verification & Trust
            'is_verified' => (bool) $listing->is_verified,
            'revenue_verified' => (bool) $listing->revenue_verified,
            'traffic_verified' => (bool) $listing->traffic_verified,
            'is_featured' => (bool) $listing->is_featured,
            
            // Details
            'domain_name' => $listing->domain_name,
            'website_url' => $listing->website_url,
            'niche' => $listing->niche,
            'view_count' => (int) $listing->view_count,
            'created_at' => $listing->created_at->format('M d, Y'),
            'age_days' => $listing->created_at->diffInDays(now()),
            
            // Auction specific
            'auction_end' => $listing->auction_end ? $listing->auction_end->format('M d, Y H:i') : null,
            'auction_ends_in' => $listing->auction_end && $listing->auction_end->isFuture() 
                ? $listing->auction_end->diffForHumans() 
                : null,
            
            // Calculated scores
            'value_score' => $valueScore,
        ];
    }

    /**
     * Calculate a value score for the listing (0-100)
     */
    private function calculateValueScore(Listing $listing, float $price): int
    {
        $score = 50; // Base score
        
        $monthlyProfit = (float) $listing->monthly_profit;
        $monthlyRevenue = (float) $listing->monthly_revenue;
        $yearlyProfit = $monthlyProfit * 12;
        
        // Profit multiple scoring (lower is better)
        if ($monthlyProfit > 0 && $price > 0) {
            $profitMultiple = $price / $yearlyProfit;
            if ($profitMultiple <= 2) $score += 20;
            elseif ($profitMultiple <= 3) $score += 15;
            elseif ($profitMultiple <= 4) $score += 10;
            elseif ($profitMultiple <= 5) $score += 5;
            elseif ($profitMultiple > 6) $score -= 10;
        }
        
        // Profit margin scoring
        if ($monthlyRevenue > 0) {
            $margin = ($monthlyProfit / $monthlyRevenue) * 100;
            if ($margin >= 50) $score += 15;
            elseif ($margin >= 30) $score += 10;
            elseif ($margin >= 20) $score += 5;
            elseif ($margin < 10) $score -= 5;
        }
        
        // Verification bonuses
        if ($listing->is_verified) $score += 5;
        if ($listing->revenue_verified) $score += 5;
        if ($listing->traffic_verified) $score += 5;
        
        // Featured bonus
        if ($listing->is_featured) $score += 3;
        
        // Popularity (view count)
        if ($listing->view_count > 1000) $score += 5;
        elseif ($listing->view_count > 500) $score += 3;
        elseif ($listing->view_count > 100) $score += 1;
        
        return max(0, min(100, $score));
    }
}
