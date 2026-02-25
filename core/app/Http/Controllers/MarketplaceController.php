<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Listing;
use App\Services\EscrowFeeCalculator;
use App\Models\ListingCategory;
use App\Models\ListingQuestion;
use App\Models\ListingView;
use App\Models\NdaDocument;
use App\Models\Review;
use App\Models\SavedSearch;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Marketplace - Buy & Sell Online Businesses';

        // Cache categories for 1 hour since they don't change frequently
        $categories = Cache::remember('marketplace_categories_with_count', 3600, function () {
            return ListingCategory::active()
                ->withCount(['listings' => function ($q) {
                    $q->where('status', Status::LISTING_ACTIVE);
                }])
                ->orderBy('sort_order')
                ->get()
                ->groupBy('business_type');
        });

        // Featured listings with optimized eager loading
        $featuredListings = Listing::active()
            ->featured()
            ->with([
                'primaryImage', // Use primaryImage instead of all images for performance
                'seller:id,username,firstname,lastname,email',
                'listingCategory:id,name,slug'
            ])
            ->orderBy('featured_until', 'desc')
            ->take(6)
            ->get();

        // Ending soon auctions with optimized eager loading
        $endingSoon = Listing::activeAuctions()
            ->endingSoon()
            ->with([
                'primaryImage',
                'seller:id,username,firstname,lastname,email'
            ])
            ->orderBy('auction_end')
            ->take(6)
            ->get();

        // Latest listings with optimized eager loading
        $latestListings = Listing::active()
            ->with([
                'primaryImage',
                'seller:id,username,firstname,lastname,email',
                'listingCategory:id,name,slug'
            ])
            ->orderBy('approved_at', 'desc')
            ->take(12)
            ->get();

        $response = view('Template::marketplace.index', compact(
            'pageTitle',
            'featuredListings',
            'endingSoon',
            'latestListings',
            'categories'
        ));

        // Add cache headers for better performance
        return $response->withHeaders([
            'Cache-Control' => 'public, max-age=300', // 5 minutes
            'Vary' => 'Accept-Encoding'
        ]);
    }

    public function browse(Request $request)
    {
        $pageTitle = 'Browse Listings';

        // For confidential listings, we need to check NDA access
        $listings = Listing::active()
            ->with([
                'primaryImage', // More efficient than loading all images
                'seller:id,username,firstname,lastname,email',
                'listingCategory:id,name,slug'
            ])
            ->where(function ($q) {
                // Show non-confidential listings to everyone
                $q->where('is_confidential', false);
                
                // Show confidential listings only to authorized users
                if (auth()->check()) {
                    $q->orWhere(function ($confidentialQ) {
                        $confidentialQ->where('is_confidential', true)
                            ->where(function ($accessQ) {
                                // Seller can see their own
                                $accessQ->where('user_id', auth()->id())
                                    // Or user has signed NDA
                                    ->orWhereHas('signedNdas', function ($ndaQ) {
                                        $ndaQ->where('user_id', auth()->id());
                                    });
                            });
                    });
                }
            })
            // Business Type Filter
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            // Sale Type Filter
            ->when($request->sale_type, function ($q, $type) {
                return $q->where('sale_type', $type);
            })
            // Category Filter
            ->when($request->category, function ($q, $categoryId) {
                return $q->where('listing_category_id', $categoryId);
            })
            // Price Range Filter (handles both fixed price and auction)
            ->when($request->min_price, function ($q, $min) {
                return $q->where(function ($query) use ($min) {
                    $query->where(function ($q) use ($min) {
                        $q->where('sale_type', 'fixed_price')
                            ->where('asking_price', '>=', $min);
                    })->orWhere(function ($q) use ($min) {
                        $q->where('sale_type', 'auction')
                            ->where(function ($subQ) use ($min) {
                                $subQ->where('current_bid', '>=', $min)
                                    ->orWhere('starting_bid', '>=', $min);
                            });
                    });
                });
            })
            ->when($request->max_price, function ($q, $max) {
                return $q->where(function ($query) use ($max) {
                    $query->where(function ($q) use ($max) {
                        $q->where('sale_type', 'fixed_price')
                            ->where('asking_price', '<=', $max);
                    })->orWhere(function ($q) use ($max) {
                        $q->where('sale_type', 'auction')
                            ->where(function ($subQ) use ($max) {
                                $subQ->where('current_bid', '<=', $max)
                                    ->orWhere('starting_bid', '<=', $max);
                            });
                    });
                });
            })
            // Revenue Range Filter
            ->when($request->min_revenue, function ($q, $min) {
                return $q->where('monthly_revenue', '>=', $min);
            })
            ->when($request->max_revenue, function ($q, $max) {
                return $q->where('monthly_revenue', '<=', $max);
            })
            // Traffic Range Filter (NEW)
            ->when($request->min_traffic, function ($q, $min) {
                return $q->where('monthly_visitors', '>=', $min);
            })
            ->when($request->max_traffic, function ($q, $max) {
                return $q->where('monthly_visitors', '<=', $max);
            })
            // Age Filter (NEW) - based on domain age
            ->when($request->min_age, function ($q, $min) {
                return $q->where('domain_age_years', '>=', $min);
            })
            ->when($request->max_age, function ($q, $max) {
                return $q->where('domain_age_years', '<=', $max);
            })
            // Verified Filter
            ->when($request->verified, function ($q) {
                return $q->where('is_verified', true);
            })
            // Featured Filter (NEW)
            ->when($request->featured === '1', function ($q) {
                return $q->featured();
            })
            // Monetization Methods Filter (NEW)
            ->when($request->monetization, function ($q, $methods) {
                if (is_array($methods)) {
                    foreach ($methods as $method) {
                        $q->whereJsonContains('monetization_methods', $method);
                    }
                } else {
                    $q->whereJsonContains('monetization_methods', $methods);
                }
                return $q;
            })
            // Traffic Sources Filter (NEW)
            ->when($request->traffic_source, function ($q, $sources) {
                if (is_array($sources)) {
                    foreach ($sources as $source) {
                        $q->whereJsonContains('traffic_sources', $source);
                    }
                } else {
                    $q->whereJsonContains('traffic_sources', $sources);
                }
                return $q;
            })
            // Search Filter – apply whenever user provides a non-empty search term
            ->when($request->filled('search'), function ($q) use ($request) {
                return $q->search($request->input('search'));
            })
            // Sort Options (Enhanced)
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
                    case 'traffic_high':
                        return $q->orderBy('monthly_visitors', 'desc');
                    case 'traffic_low':
                        return $q->orderBy('monthly_visitors', 'asc');
                    case 'ending_soon':
                        return $q->whereNotNull('auction_end')
                            ->where('auction_end', '>', now())
                            ->orderBy('auction_end');
                    case 'most_bids':
                        return $q->orderBy('total_bids', 'desc');
                    case 'most_watched':
                        return $q->orderBy('watchlist_count', 'desc');
                    case 'most_viewed':
                        return $q->orderBy('view_count', 'desc');
                    case 'oldest':
                        return $q->orderBy('approved_at', 'asc');
                    case 'newest':
                    default:
                        return $q->orderBy('approved_at', 'desc');
                }
            }, function ($q) {
                return $q->orderBy('approved_at', 'desc');
            })
            ->paginate(getPaginate());

        // Cache categories and business types for better performance
        $categories = Cache::remember('marketplace_categories', 3600, function () {
            return ListingCategory::active()->orderBy('sort_order')->get();
        });

        $businessTypes = Cache::remember('marketplace_business_types', 3600, function () {
            return $this->getBusinessTypes();
        });

        // Get saved searches for logged-in users with caching
        $savedSearches = [];
        if (auth()->check()) {
            $savedSearches = Cache::remember(
                'user_saved_searches_' . auth()->id(),
                1800, // 30 minutes
                function () {
                    return SavedSearch::where('user_id', auth()->id())
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                }
            );
        }

        $response = view('Template::marketplace.browse', compact(
            'pageTitle',
            'listings',
            'categories',
            'businessTypes',
            'savedSearches'
        ));

        // Add cache headers (shorter for search results)
        return $response->withHeaders([
            'Cache-Control' => 'public, max-age=60', // 1 minute
            'Vary' => 'Accept-Encoding'
        ]);
    }

    public function show($slug)
    {
        $listing = Listing::where('slug', $slug)
            ->where('status', '!=', Status::LISTING_DRAFT)
            ->with([
                'images', // Keep all images for detail view
                'seller:id,username,firstname,lastname,email,created_at',
                'listingCategory:id,name,slug',
                'metrics' => function ($q) {
                    $q->orderBy('period_date', 'desc')->take(12);
                },
                'questions' => function ($q) {
                    $q->where('is_public', true)
                        ->where('status', Status::QUESTION_ANSWERED)
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('answered_at', 'desc');
                },
                'bids' => function ($q) {
                    $q->orderBy('amount', 'desc')->take(10);
                },
                'winner:id,username,firstname,lastname',
                'highestBidder:id,username,firstname,lastname'
            ])
            ->firstOrFail();

        // Deactivated listing: only the seller may see it; everyone else gets "Listing not available"
        if ($listing->is_deactivated) {
            if (!auth()->check() || auth()->id() !== $listing->user_id) {
                return response()->view('Template::marketplace.not_available', [
                    'pageTitle' => __('Listing Not Available'),
                    'message' => __('This listing is no longer available.'),
                ], 404);
            }
        }

        // Only show active (not in escrow), sold listings or own listings
        // If listing is in escrow (has escrow_id), only show to seller/buyer
        if ($listing->escrow_id && $listing->status !== Status::LISTING_SOLD) {
            $isSeller = auth()->check() && auth()->id() === $listing->user_id;
            $isBuyer = auth()->check() && $listing->winner_id && auth()->id() === $listing->winner_id;
            if (!$isSeller && !$isBuyer) {
                abort(404);
            }
        }
        
        if (!in_array($listing->status, [Status::LISTING_ACTIVE, Status::LISTING_SOLD])) {
            if (!auth()->check() || auth()->id() !== $listing->user_id) {
                abort(404);
            }
        }

        // Check if listing is confidential and requires NDA
        if ($listing->is_confidential && $listing->requires_nda) {
            // Allow seller to view
            if (auth()->check() && auth()->id() === $listing->user_id) {
                // Seller can view
            } 
            // Allow buyer/winner to view
            elseif (auth()->check() && ($listing->winner_id === auth()->id() || $listing->highest_bidder_id === auth()->id())) {
                // Buyer can view
            }
            // Check if user has signed NDA
            elseif (!auth()->check() || !$listing->hasSignedNda()) {
                // Redirect to NDA signing page
                return redirect()->route('marketplace.nda.show', $listing->id);
            }
        }

        $pageTitle = $listing->title;

        // Prepare SEO data
        $seoDescription = $listing->tagline ?? strip_tags(Str::limit($listing->description, 160));
        if ($listing->monthly_revenue > 0) {
            $seoDescription .= ' | Monthly Revenue: ' . showAmount($listing->monthly_revenue);
        }
        if ($listing->asking_price > 0) {
            $seoDescription .= ' | Price: ' . showAmount($listing->asking_price);
        }
        
        $seoImage = $listing->primaryImage 
            ? getImage(getFilePath('listing') . '/' . $listing->primaryImage->image)
            : getImage(getFilePath('seo') . '/' . gs('seo_image'));
        
        $seoContents = (object) [
            'description' => $seoDescription,
            'social_title' => $listing->title . ' - ' . gs('site_name'),
            'social_description' => $seoDescription,
            'keywords' => array_filter([
                $listing->business_type,
                $listing->listingCategory->name ?? null,
                $listing->domain_name ?? null,
                'online business',
                'for sale',
                'marketplace'
            ])
        ];
        
        $seoImage = $seoImage;

        // Track view
        $this->trackView($listing);

        // Get seller info
        $seller = $listing->seller;
        $sellerStats = [
            'total_sales' => $seller->total_sales,
            'avg_rating' => $seller->avg_rating,
            'total_reviews' => $seller->total_reviews,
            'member_since' => $seller->created_at->format('M Y'),
            'active_listings' => $seller->activeListings()->count(),
        ];

        // Seller reviews
        $sellerReviews = Review::where('reviewed_user_id', $seller->id)
            ->approved()
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Similar listings (exclude confidential ones user can't access)
        $similarListings = Listing::active()
            ->where('id', '!=', $listing->id)
            ->where('business_type', $listing->business_type)
            ->where(function ($q) {
                $q->where('is_confidential', false);
                if (auth()->check()) {
                    $q->orWhere(function ($subQ) {
                        $subQ->where('is_confidential', true)
                            ->where(function ($ndaQ) {
                                $ndaQ->where('user_id', auth()->id())
                                    ->orWhereHas('signedNdas', function ($signedQ) {
                                        $signedQ->where('user_id', auth()->id());
                                    });
                            });
                    });
                }
            })
            ->with(['images', 'seller'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Check if user is watching
        $isWatching = false;
        if (auth()->check()) {
            $isWatching = Watchlist::where('user_id', auth()->id())
                ->where('listing_id', $listing->id)
                ->exists();
        }

        // Buyer fee breakdown: what the buyer will pay (listing price + fees) for display
        $paymentMode = ($listing->payout_method ?? 'system') === 'direct' ? 'direct' : 'system';
        $buyerFeeBreakdown = null;
        if ($listing->sale_type === 'auction') {
            $buyNowAmount = (float) ($listing->buy_now_price ?? 0);
            if ($buyNowAmount > 0) {
                $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees($buyNowAmount, 'escrow_service_fee', $paymentMode);
                $buyerFee = (float) ($feeBreakdown['buyer'] ?? $feeBreakdown['total'] ?? 0);
                $totalBuyerPays = $paymentMode === 'direct' ? $buyNowAmount : ($buyNowAmount + $buyerFee);
                $buyerFeeBreakdown = [
                    'label' => __('Buy Now price'),
                    'amount' => $buyNowAmount,
                    'buyer_fee' => $buyerFee,
                    'total' => $totalBuyerPays,
                    'fees' => $feeBreakdown['fees'] ?? [],
                    'payment_mode' => $paymentMode,
                ];
            }
            $bidAmount = (float) ($listing->starting_bid ?? 0);
            if ($bidAmount > 0 && !$buyerFeeBreakdown) {
                $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees($bidAmount, 'escrow_service_fee', $paymentMode);
                $buyerFee = (float) ($feeBreakdown['buyer'] ?? $feeBreakdown['total'] ?? 0);
                $totalBuyerPays = $paymentMode === 'direct' ? $bidAmount : ($bidAmount + $buyerFee);
                $buyerFeeBreakdown = [
                    'label' => __('Min. bid'),
                    'amount' => $bidAmount,
                    'buyer_fee' => $buyerFee,
                    'total' => $totalBuyerPays,
                    'fees' => $feeBreakdown['fees'] ?? [],
                    'payment_mode' => $paymentMode,
                    'is_bid_example' => true,
                ];
            }
        } else {
            $amount = (float) ($listing->asking_price ?? 0);
            if ($amount > 0) {
                $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees($amount, 'escrow_service_fee', $paymentMode);
                $buyerFee = (float) ($feeBreakdown['buyer'] ?? $feeBreakdown['total'] ?? 0);
                $totalBuyerPays = $paymentMode === 'direct' ? $amount : ($amount + $buyerFee);
                $buyerFeeBreakdown = [
                    'label' => __('Asking price'),
                    'amount' => $amount,
                    'buyer_fee' => $buyerFee,
                    'total' => $totalBuyerPays,
                    'fees' => $feeBreakdown['fees'] ?? [],
                    'payment_mode' => $paymentMode,
                ];
            }
        }

        // Payback calculator data: total you pay + payback months from listing revenue/profit
        $paybackData = null;
        if ($buyerFeeBreakdown) {
            $totalBuyerPays = (float) ($buyerFeeBreakdown['total'] ?? 0);
            $monthlyAmount = 0.0;
            $label = '';
            if ((float) ($listing->monthly_profit ?? 0) > 0) {
                $monthlyAmount = (float) $listing->monthly_profit;
                $label = __('monthly profit');
            } elseif ((float) ($listing->monthly_revenue ?? 0) > 0) {
                $monthlyAmount = (float) $listing->monthly_revenue;
                $label = __('monthly revenue');
            } elseif ((float) ($listing->yearly_profit ?? 0) > 0) {
                $monthlyAmount = (float) $listing->yearly_profit / 12;
                $label = __('yearly profit') . ' / 12';
            } elseif ((float) ($listing->yearly_revenue ?? 0) > 0) {
                $monthlyAmount = (float) $listing->yearly_revenue / 12;
                $label = __('yearly revenue') . ' / 12';
            }
            $canCalculate = $monthlyAmount > 0 && $totalBuyerPays > 0;
            $paybackMonths = null;
            $paybackOk = false;
            $message = null;
            if (!$canCalculate) {
                $message = __("Payback cannot be calculated because this listing doesn't include revenue or profit data.");
            } else {
                $paybackMonths = $totalBuyerPays / $monthlyAmount;
                $paybackOk = $paybackMonths <= 24;
            }
            $paybackData = [
                'total' => $totalBuyerPays,
                'monthly_amount' => $monthlyAmount,
                'payback_months' => $paybackMonths,
                'payback_ok' => $paybackOk,
                'label' => $label,
                'can_calculate' => $canCalculate,
                'message' => $message,
            ];
        }

        return view('Template::marketplace.show', compact(
            'pageTitle',
            'listing',
            'seller',
            'sellerStats',
            'sellerReviews',
            'similarListings',
            'isWatching',
            'seoContents',
            'seoImage',
            'buyerFeeBreakdown',
            'paybackData'
        ));
    }

    public function askQuestion(Request $request, $id)
    {
        if (!auth()->check()) {
            $notify[] = ['error', 'Please login to ask a question'];
            return back()->withNotify($notify);
        }

        $listing = Listing::active()->findOrFail($id);

        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        // Cannot ask question on own listing
        if ($listing->user_id === auth()->id()) {
            $notify[] = ['error', 'You cannot ask questions on your own listing'];
            return back()->withNotify($notify);
        }

        $question = new ListingQuestion();
        $question->listing_id = $listing->id;
        $question->user_id = auth()->id();
        $question->question = $request->question;
        $question->status = Status::QUESTION_PENDING;
        $question->save();

        // Notify seller
        notify($listing->seller, 'NEW_QUESTION_RECEIVED', [
            'listing_title' => $listing->title,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
            'question' => $request->question,
            'asker' => auth()->user()->username,
            'asker_username' => auth()->user()->username,
        ]);

        $notify[] = ['success', 'Your question has been submitted'];
        return back()->withNotify($notify);
    }

    public function sellerProfile($username)
    {
        $seller = \App\Models\User::where('username', $username)
            ->active()
            ->firstOrFail();

        $pageTitle = $seller->fullname . ' - Seller Profile';

        $listings = Listing::active()
            ->where('user_id', $seller->id)
            ->with(['images', 'listingCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        $stats = [
            'total_sales' => $seller->total_sales,
            'total_listings' => $seller->total_listings,
            'avg_rating' => $seller->avg_rating,
            'total_reviews' => $seller->total_reviews,
            'member_since' => $seller->created_at,
            'is_verified' => $seller->is_verified_seller,
        ];

        $reviews = Review::where('reviewed_user_id', $seller->id)
            ->approved()
            ->with(['reviewer', 'listing'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('Template::marketplace.seller', compact(
            'pageTitle',
            'seller',
            'listings',
            'stats',
            'reviews'
        ));
    }

    public function category($slug)
    {
        $category = ListingCategory::where('slug', $slug)->active()->firstOrFail();

        $pageTitle = $category->name;

        $listings = Listing::active()
            ->where('listing_category_id', $category->id)
            ->with(['images', 'seller'])
            ->orderBy('approved_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::marketplace.category', compact('pageTitle', 'category', 'listings'));
    }

    public function businessType($type)
    {
        $businessTypes = $this->getBusinessTypes();

        if (!isset($businessTypes[$type])) {
            abort(404);
        }

        $pageTitle = $businessTypes[$type];

        $listings = Listing::active()
            ->where('business_type', $type)
            ->with(['images', 'seller', 'listingCategory'])
            ->orderBy('approved_at', 'desc')
            ->paginate(getPaginate());

        $categories = ListingCategory::active()
            ->where('business_type', $type)
            ->withCount(['listings' => function ($q) {
                $q->where('status', Status::LISTING_ACTIVE);
            }])
            ->orderBy('sort_order')
            ->get();

        return view('Template::marketplace.business_type', compact(
            'pageTitle',
            'type',
            'listings',
            'categories',
            'businessTypes'
        ));
    }

    public function auctions(Request $request)
    {
        $pageTitle = 'Live Auctions';

        $listings = Listing::activeAuctions()
            ->with(['images', 'seller', 'listingCategory'])
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->ending === 'soon', function ($q) {
                return $q->where('auction_end', '<=', now()->addHours(24));
            })
            ->orderBy('auction_end')
            ->paginate(getPaginate());

        $businessTypes = $this->getBusinessTypes();

        return view('Template::marketplace.auctions', compact(
            'pageTitle',
            'listings',
            'businessTypes'
        ));
    }

    private function trackView($listing)
    {
        $ipAddress = request()->ip();
        $userId = auth()->id() ?? 0;

        // Check for recent view from same IP/user
        $recentView = ListingView::where('listing_id', $listing->id)
            ->where(function ($q) use ($userId, $ipAddress) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ipAddress);
                }
            })
            ->where('created_at', '>', now()->subHour())
            ->exists();

        if (!$recentView) {
            ListingView::create([
                'listing_id' => $listing->id,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('referer'),
            ]);

            $listing->incrementViews();
        }
    }

    private function getBusinessTypes()
    {
        return [
            'domain' => 'Domain Names',
            'website' => 'Websites',
            'social_media_account' => 'Social Media Accounts',
            'mobile_app' => 'Mobile Apps',
            'desktop_app' => 'Desktop Apps',
        ];
    }
}

