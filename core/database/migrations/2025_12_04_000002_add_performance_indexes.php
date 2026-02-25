<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Listings table indexes
        Schema::table('listings', function (Blueprint $table) {
            // Composite indexes for common queries
            $table->index(['status', 'sale_type', 'business_type'], 'listings_status_sale_business_idx');
            $table->index(['user_id', 'status'], 'listings_user_status_idx');
            $table->index(['auction_end', 'status'], 'listings_auction_end_status_idx');
            $table->index(['created_at', 'status'], 'listings_created_status_idx');
            $table->index(['current_bid', 'status'], 'listings_current_bid_status_idx');
            $table->index(['is_featured', 'featured_until'], 'listings_featured_until_idx');

            // Search optimization
            $table->index(['title'], 'listings_title_idx');
            $table->index(['domain_name'], 'listings_domain_idx');
            $table->index(['business_type'], 'listings_business_type_idx');
            $table->index(['sale_type'], 'listings_sale_type_idx');

            // Foreign key indexes (if not already present)
            $table->index(['user_id'], 'listings_user_id_idx');
            $table->index(['winner_id'], 'listings_winner_id_idx');
            $table->index(['highest_bidder_id'], 'listings_highest_bidder_id_idx');
            $table->index(['listing_category_id'], 'listings_category_id_idx');
            $table->index(['escrow_id'], 'listings_escrow_id_idx');
        });

        // Bids table indexes
        Schema::table('bids', function (Blueprint $table) {
            // Composite indexes for bid queries
            $table->index(['listing_id', 'status'], 'bids_listing_status_idx');
            $table->index(['user_id', 'status'], 'bids_user_status_idx');
            $table->index(['listing_id', 'amount'], 'bids_listing_amount_idx');
            $table->index(['created_at', 'status'], 'bids_created_status_idx');

            // Foreign key indexes
            $table->index(['listing_id'], 'bids_listing_id_idx');
            $table->index(['user_id'], 'bids_user_id_idx');
        });

        // Escrows table indexes
        Schema::table('escrows', function (Blueprint $table) {
            // Composite indexes for escrow queries
            $table->index(['status', 'created_at'], 'escrows_status_created_idx');
            $table->index(['buyer_id', 'status'], 'escrows_buyer_status_idx');
            $table->index(['seller_id', 'status'], 'escrows_seller_status_idx');
            $table->index(['amount', 'status'], 'escrows_amount_status_idx');

            // Foreign key indexes
            $table->index(['seller_id'], 'escrows_seller_id_idx');
            $table->index(['buyer_id'], 'escrows_buyer_id_idx');
            $table->index(['creator_id'], 'escrows_creator_id_idx');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Additional indexes for user queries
            $table->index(['status', 'ev', 'sv'], 'users_status_verification_idx');
            $table->index(['balance'], 'users_balance_idx');
            $table->index(['created_at', 'status'], 'users_created_status_idx');
            $table->index(['username'], 'users_username_idx');
            $table->index(['email'], 'users_email_idx');
        });

        // Offers table indexes
        Schema::table('offers', function (Blueprint $table) {
            $table->index(['listing_id', 'status'], 'offers_listing_status_idx');
            $table->index(['buyer_id', 'status'], 'offers_buyer_status_idx');
            $table->index(['seller_id', 'status'], 'offers_seller_status_idx');
            $table->index(['created_at', 'status'], 'offers_created_status_idx');
        });

        // Reviews table indexes
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['listing_id', 'status'], 'reviews_listing_status_idx');
            $table->index(['reviewer_id'], 'reviews_reviewer_id_idx');
            $table->index(['reviewed_user_id'], 'reviews_reviewed_user_id_idx');
        });

        // Watchlist table indexes
        Schema::table('watchlist', function (Blueprint $table) {
            $table->index(['user_id', 'listing_id'], 'watchlist_user_listing_idx');
            $table->index(['listing_id'], 'watchlist_listing_id_idx');
        });

        // Transactions table indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'transactions_user_created_idx');
            $table->index(['trx_type', 'created_at'], 'transactions_type_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all indexes created in this migration
        $tables = [
            'listings', 'bids', 'escrows', 'users',
            'offers', 'reviews', 'watchlist', 'transactions'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop indexes (this is a simplified approach - in production you'd want to be more specific)
                try {
                    // This will attempt to drop common index patterns
                    $indexes = [
                        "{$tableName}_status_sale_business_idx",
                        "{$tableName}_user_status_idx",
                        "{$tableName}_auction_end_status_idx",
                        "{$tableName}_created_status_idx",
                        "{$tableName}_current_bid_status_idx",
                        "{$tableName}_featured_until_idx",
                        "{$tableName}_title_idx",
                        "{$tableName}_domain_idx",
                        "{$tableName}_business_type_idx",
                        "{$tableName}_sale_type_idx",
                        "{$tableName}_user_id_idx",
                        "{$tableName}_winner_id_idx",
                        "{$tableName}_highest_bidder_id_idx",
                        "{$tableName}_category_id_idx",
                        "{$tableName}_escrow_id_idx",
                        "{$tableName}_listing_status_idx",
                        "{$tableName}_listing_amount_idx",
                        "{$tableName}_buyer_status_idx",
                        "{$tableName}_seller_status_idx",
                        "{$tableName}_amount_status_idx",
                        "{$tableName}_seller_id_idx",
                        "{$tableName}_buyer_id_idx",
                        "{$tableName}_creator_id_idx",
                        "{$tableName}_status_verification_idx",
                        "{$tableName}_balance_idx",
                        "{$tableName}_username_idx",
                        "{$tableName}_email_idx",
                        "{$tableName}_user_listing_idx",
                        "{$tableName}_listing_id_idx",
                        "{$tableName}_user_created_idx",
                        "{$tableName}_type_created_idx"
                    ];

                    foreach ($indexes as $index) {
                        try {
                            $table->dropIndex($index);
                        } catch (\Exception $e) {
                            // Index might not exist, continue
                        }
                    }
                } catch (\Exception $e) {
                    // Table might not exist or other issues
                }
            });
        }
    }
};
