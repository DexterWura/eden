<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MarketplaceValidator
{
    /**
     * Validate auction business rules
     */
    public static function validateAuctionRules(array $data, ?Listing $existingListing = null): array
    {
        $errors = [];

        // Reserve price validation
        if (isset($data['reserve_price']) && $data['reserve_price'] > 0) {
            if (!isset($data['starting_bid']) || $data['reserve_price'] <= $data['starting_bid']) {
                $errors[] = 'Reserve price must be higher than the starting bid';
            }
        }

        // Buy now price validation
        if (isset($data['buy_now_price']) && $data['buy_now_price'] > 0) {
            if (!isset($data['starting_bid']) || $data['buy_now_price'] <= $data['starting_bid']) {
                $errors[] = 'Buy now price must be higher than the starting bid';
            }
        }

        // Bid increment validation
        if (isset($data['bid_increment']) && $data['bid_increment'] > 0) {
            if (isset($data['starting_bid']) && $data['bid_increment'] > $data['starting_bid'] * 0.5) {
                $errors[] = 'Bid increment cannot be more than 50% of the starting bid';
            }
        }

        // Auction duration validation
        if (isset($data['auction_duration'])) {
            $minDays = \App\Models\MarketplaceSetting::minAuctionDays();
            $maxDays = \App\Models\MarketplaceSetting::maxAuctionDays();

            if ($data['auction_duration'] < $minDays || $data['auction_duration'] > $maxDays) {
                $errors[] = "Auction duration must be between {$minDays} and {$maxDays} days";
            }
        }

        return $errors;
    }

    /**
     * Validate financial data consistency
     */
    public static function validateFinancialData(array $data): array
    {
        $errors = [];

        // Revenue vs profit validation
        if (isset($data['monthly_profit']) && isset($data['monthly_revenue'])) {
            if ($data['monthly_profit'] > $data['monthly_revenue']) {
                $errors[] = 'Monthly profit cannot exceed monthly revenue';
            }
        }

        if (isset($data['yearly_profit']) && isset($data['yearly_revenue'])) {
            if ($data['yearly_profit'] > $data['yearly_revenue']) {
                $errors[] = 'Yearly profit cannot exceed yearly revenue';
            }
        }

        // Negative values validation
        $financialFields = [
            'monthly_revenue', 'monthly_profit', 'yearly_revenue', 'yearly_profit',
            'asking_price', 'starting_bid', 'reserve_price', 'buy_now_price', 'current_bid'
        ];

        foreach ($financialFields as $field) {
            if (isset($data[$field]) && $data[$field] < 0) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' cannot be negative';
            }
        }

        return $errors;
    }

    /**
     * Validate business type specific requirements
     */
    public static function validateBusinessTypeRequirements(array $data): array
    {
        $errors = [];
        $businessType = $data['business_type'] ?? null;

        if (!$businessType) {
            $errors[] = 'Business type is required';
            return $errors;
        }

        switch ($businessType) {
            case 'domain':
                if (empty($data['domain_name'])) {
                    $errors[] = 'Domain name is required for domain listings';
                } elseif (!self::isValidDomain($data['domain_name'])) {
                    $errors[] = 'Invalid domain name format';
                }
                break;

            case 'website':
                if (empty($data['website_url'])) {
                    $errors[] = 'Website URL is required for website listings';
                } elseif (!self::isValidUrl($data['website_url'])) {
                    $errors[] = 'Invalid website URL format';
                }
                break;

            case 'social_media_account':
                if (empty($data['platform'])) {
                    $errors[] = 'Social media platform is required';
                }
                break;
        }

        return $errors;
    }

    /**
     * Validate user permissions for marketplace actions
     */
    public static function validateUserPermissions(User $user, string $action, ?Listing $listing = null): array
    {
        $errors = [];

        // Check user status
        if ($user->status !== Status::USER_ACTIVE) {
            $errors[] = 'Your account must be active to perform this action';
        }

        // Check email verification
        if ($user->ev !== Status::VERIFIED) {
            $errors[] = 'Email verification is required';
        }

        // Action-specific validations
        switch ($action) {
            case 'create_listing':
                // Check if user has reached listing limit
                $recentListings = Listing::where('user_id', $user->id)
                    ->where('created_at', '>', now()->subDays(30))
                    ->count();

                if ($recentListings >= 50) { // Max 50 listings per month
                    $errors[] = 'You have reached the maximum number of listings allowed per month';
                }
                break;

            case 'place_bid':
                if (!$listing) {
                    $errors[] = 'Listing not found';
                    break;
                }

                // Cannot bid on own listing
                if ($listing->user_id === $user->id) {
                    $errors[] = 'You cannot bid on your own listing';
                }

                // Check if user already has an active bid
                $existingBid = \App\Models\Bid::where('listing_id', $listing->id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
                    ->exists();

                if ($existingBid) {
                    $errors[] = 'You already have an active bid on this listing';
                }
                break;

            case 'create_offer':
                if (!$listing) {
                    $errors[] = 'Listing not found';
                    break;
                }

                // Cannot make offer on own listing
                if ($listing->user_id === $user->id) {
                    $errors[] = 'You cannot make an offer on your own listing';
                }
                break;
        }

        return $errors;
    }

    /**
     * Validate listing status transitions
     */
    public static function validateStatusTransition(Listing $listing, int $newStatus): array
    {
        $errors = [];
        $currentStatus = $listing->status;

        // Define valid transitions
        $validTransitions = [
            Status::LISTING_DRAFT => [Status::LISTING_PENDING],
            Status::LISTING_PENDING => [Status::LISTING_ACTIVE, Status::LISTING_REJECTED],
            Status::LISTING_ACTIVE => [Status::LISTING_SOLD, Status::LISTING_EXPIRED, Status::LISTING_CANCELLED],
            Status::LISTING_SOLD => [], // Final state
            Status::LISTING_EXPIRED => [], // Final state
            Status::LISTING_CANCELLED => [], // Final state
            Status::LISTING_REJECTED => [], // Final state
        ];

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            $errors[] = 'Invalid status transition';
        }

        // Business logic validations for specific transitions
        if ($newStatus === Status::LISTING_SOLD) {
            if (!$listing->winner_id) {
                $errors[] = 'Cannot mark as sold without a winner';
            }
        }

        return $errors;
    }

    /**
     * Validate bid amount against listing rules
     */
    public static function validateBidAmount(float $amount, Listing $listing): array
    {
        $errors = [];

        if ($amount <= 0) {
            $errors[] = 'Bid amount must be greater than 0';
            return $errors;
        }

        if ($listing->sale_type !== 'auction') {
            $errors[] = 'This listing does not accept bids';
            return $errors;
        }

        if ($listing->status !== Status::LISTING_ACTIVE) {
            $errors[] = 'This listing is not active';
            return $errors;
        }

        // Check minimum bid
        $minimumBid = $listing->minimum_bid;
        if ($amount < $minimumBid) {
            $errors[] = 'Bid must be at least ' . number_format($minimumBid, 2);
        }

        // Check reserve price (don't reveal it, just validate)
        if ($listing->reserve_price > 0 && $amount < $listing->reserve_price && $listing->current_bid >= $listing->reserve_price) {
            $errors[] = 'Bid does not meet the reserve price requirement';
        }

        return $errors;
    }

    /**
     * Helper: Validate domain name
     */
    private static function isValidDomain(string $domain): bool
    {
        // Remove protocol if present
        $domain = preg_replace('/^https?:\/\//', '', $domain);

        // Basic domain validation
        return filter_var('http://' . $domain, FILTER_VALIDATE_URL) !== false &&
               preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain);
    }

    /**
     * Helper: Validate URL
     */
    private static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false &&
               preg_match('/^https?:\/\/.+/i', $url);
    }
}
