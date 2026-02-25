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
        // Add constraints to users table
        Schema::table('users', function (Blueprint $table) {
            // Ensure balance is never negative
            $table->decimal('balance', 15, 2)->default(0)->change();

            // Add check constraints for status values
            DB::statement('ALTER TABLE users ADD CONSTRAINT chk_users_status CHECK (status IN (0, 1))');
            DB::statement('ALTER TABLE users ADD CONSTRAINT chk_users_ev CHECK (ev IN (0, 1))');
            DB::statement('ALTER TABLE users ADD CONSTRAINT chk_users_sv CHECK (sv IN (0, 1))');
            DB::statement('ALTER TABLE users ADD CONSTRAINT chk_users_kv CHECK (kv IN (0, 1, 2))');
        });

        // Add constraints to listings table
        Schema::table('listings', function (Blueprint $table) {
            // Ensure financial fields are non-negative
            $table->decimal('asking_price', 15, 2)->nullable()->change();
            $table->decimal('starting_bid', 15, 2)->nullable()->change();
            $table->decimal('reserve_price', 15, 2)->nullable()->change();
            $table->decimal('buy_now_price', 15, 2)->nullable()->change();
            $table->decimal('current_bid', 15, 2)->default(0)->change();
            $table->decimal('final_price', 15, 2)->nullable()->change();
            $table->decimal('monthly_revenue', 15, 2)->default(0)->change();
            $table->decimal('monthly_profit', 15, 2)->default(0)->change();
            $table->decimal('yearly_revenue', 15, 2)->default(0)->change();
            $table->decimal('yearly_profit', 15, 2)->default(0)->change();

            // Add check constraints
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_listings_status CHECK (status BETWEEN 0 AND 6)');
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_listings_sale_type CHECK (sale_type IN ("fixed_price", "auction"))');
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_listings_business_type CHECK (business_type IN ("domain", "website", "social_media_account", "mobile_app", "desktop_app"))');

            // Business logic constraints
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_listings_financials CHECK (
                (monthly_profit <= monthly_revenue OR monthly_revenue = 0) AND
                (yearly_profit <= yearly_revenue OR yearly_revenue = 0)
            )');

            // Auction logic constraints
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_auction_prices CHECK (
                (sale_type = "fixed_price" AND asking_price > 0) OR
                (sale_type = "auction" AND starting_bid > 0)
            )');

            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_reserve_price CHECK (
                reserve_price IS NULL OR reserve_price >= starting_bid OR sale_type = "fixed_price"
            )');

            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_buy_now_price CHECK (
                buy_now_price IS NULL OR buy_now_price >= starting_bid OR sale_type = "fixed_price"
            )');
        });

        // Add constraints to bids table
        Schema::table('bids', function (Blueprint $table) {
            // Ensure bid amount is positive
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('max_bid', 15, 2)->nullable()->change();

            // Add check constraints
            DB::statement('ALTER TABLE bids ADD CONSTRAINT chk_bids_status CHECK (status BETWEEN 0 AND 5)');
            DB::statement('ALTER TABLE bids ADD CONSTRAINT chk_bids_amount_positive CHECK (amount > 0)');
            DB::statement('ALTER TABLE bids ADD CONSTRAINT chk_bids_max_bid CHECK (max_bid IS NULL OR max_bid >= amount)');
        });

        // Add constraints to escrows table
        Schema::table('escrows', function (Blueprint $table) {
            // Ensure amounts are positive
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('charge', 15, 2)->default(0)->change();
            $table->decimal('buyer_charge', 15, 2)->default(0)->change();
            $table->decimal('seller_charge', 15, 2)->default(0)->change();
            $table->decimal('paid_amount', 15, 2)->default(0)->change();

            // Add check constraints
            DB::statement('ALTER TABLE escrows ADD CONSTRAINT chk_escrows_status CHECK (status BETWEEN 0 AND 9)');
            DB::statement('ALTER TABLE escrows ADD CONSTRAINT chk_escrows_amount_positive CHECK (amount > 0)');
            DB::statement('ALTER TABLE escrows ADD CONSTRAINT chk_escrows_charges_non_negative CHECK (
                charge >= 0 AND buyer_charge >= 0 AND seller_charge >= 0 AND paid_amount >= 0
            )');
            DB::statement('ALTER TABLE escrows ADD CONSTRAINT chk_escrows_paid_amount CHECK (paid_amount <= amount + charge)');
            DB::statement('ALTER TABLE escrows ADD CONSTRAINT chk_escrows_charge_payer CHECK (charge_payer IN (1, 2))');
        });

        // Add foreign key constraints with proper cascade actions
        Schema::table('listings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('winner_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('highest_bidder_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('listing_category_id')->references('id')->on('listing_categories')->onDelete('set null');
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('escrows', function (Blueprint $table) {
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['winner_id']);
            $table->dropForeign(['highest_bidder_id']);
            $table->dropForeign(['listing_category_id']);
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('escrows', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['creator_id']);
        });

        // Drop check constraints
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_users_status');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_users_ev');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_users_sv');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_users_kv');

        DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS chk_listings_status');
        DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS chk_listings_sale_type');
        DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS chk_listings_business_type');
        DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS chk_listings_financials');
        DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS chk_auction_prices');
        DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS chk_reserve_price');
        DB::statement('ALTER TABLE listings DROP CONSTRAINT IF EXISTS chk_buy_now_price');

        DB::statement('ALTER TABLE bids DROP CONSTRAINT IF EXISTS chk_bids_status');
        DB::statement('ALTER TABLE bids DROP CONSTRAINT IF EXISTS chk_bids_amount_positive');
        DB::statement('ALTER TABLE bids DROP CONSTRAINT IF EXISTS chk_bids_max_bid');

        DB::statement('ALTER TABLE escrows DROP CONSTRAINT IF EXISTS chk_escrows_status');
        DB::statement('ALTER TABLE escrows DROP CONSTRAINT IF EXISTS chk_escrows_amount_positive');
        DB::statement('ALTER TABLE escrows DROP CONSTRAINT IF EXISTS chk_escrows_charges_non_negative');
        DB::statement('ALTER TABLE escrows DROP CONSTRAINT IF EXISTS chk_escrows_paid_amount');
        DB::statement('ALTER TABLE escrows DROP CONSTRAINT IF EXISTS chk_escrows_charge_payer');
    }
};
