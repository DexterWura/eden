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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('listing_number', 40)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('listing_category_id')->nullable();
            
            // Basic Info
            $table->string('title', 255);
            $table->string('slug', 300)->unique();
            $table->text('tagline')->nullable();
            $table->longText('description');
            
            // Business Type: domain, website, social_media_account, mobile_app, desktop_app
            $table->enum('business_type', ['domain', 'website', 'social_media_account', 'mobile_app', 'desktop_app']);
            
            // Sale Type
            $table->enum('sale_type', ['fixed_price', 'auction'])->default('fixed_price');
            
            // Pricing
            $table->decimal('asking_price', 28, 8)->default(0);
            $table->decimal('reserve_price', 28, 8)->default(0)->comment('Minimum price for auction');
            $table->decimal('buy_now_price', 28, 8)->default(0)->comment('Instant purchase price for auctions');
            $table->decimal('starting_bid', 28, 8)->default(0)->comment('Starting bid for auctions');
            $table->decimal('bid_increment', 28, 8)->default(1)->comment('Minimum bid increment');
            $table->decimal('current_bid', 28, 8)->default(0);
            $table->unsignedBigInteger('highest_bidder_id')->default(0);
            $table->integer('total_bids')->default(0);
            
            // Business Details
            $table->string('url', 500)->nullable()->comment('Website/app URL');
            $table->string('domain_name', 255)->nullable();
            $table->string('domain_extension', 50)->nullable();
            $table->string('domain_registrar', 100)->nullable();
            $table->date('domain_expiry')->nullable();
            $table->integer('domain_age_years')->default(0);
            
            // Social Media Specifics
            $table->string('platform', 100)->nullable()->comment('Instagram, YouTube, TikTok, etc.');
            $table->string('niche', 100)->nullable();
            $table->bigInteger('followers_count')->default(0);
            $table->bigInteger('subscribers_count')->default(0);
            $table->decimal('engagement_rate', 8, 4)->default(0);
            
            // App Specifics
            $table->string('app_store_url', 500)->nullable();
            $table->string('play_store_url', 500)->nullable();
            $table->bigInteger('downloads_count')->default(0);
            $table->decimal('app_rating', 3, 2)->default(0);
            $table->string('tech_stack', 500)->nullable();
            
            // Financials
            $table->decimal('monthly_revenue', 28, 8)->default(0);
            $table->decimal('monthly_profit', 28, 8)->default(0);
            $table->decimal('yearly_revenue', 28, 8)->default(0);
            $table->decimal('yearly_profit', 28, 8)->default(0);
            $table->integer('revenue_multiple')->default(0)->comment('Asking price / yearly profit');
            
            // Traffic
            $table->bigInteger('monthly_visitors', false, true)->default(0);
            $table->bigInteger('monthly_page_views', false, true)->default(0);
            $table->string('traffic_sources', 500)->nullable()->comment('JSON: organic, paid, social, etc.');
            
            // Monetization
            $table->string('monetization_methods', 500)->nullable()->comment('JSON: ads, affiliate, products, etc.');
            
            // Assets Included
            $table->text('assets_included')->nullable()->comment('JSON: domain, content, email list, etc.');
            
            // Verification
            $table->boolean('is_verified')->default(false);
            $table->boolean('revenue_verified')->default(false);
            $table->boolean('traffic_verified')->default(false);
            $table->text('verification_notes')->nullable();
            
            // Listing Status
            // 0: draft, 1: pending_approval, 2: active, 3: sold, 4: expired, 5: cancelled, 6: rejected
            $table->tinyInteger('status')->default(0);
            $table->text('rejection_reason')->nullable();
            
            // Timing
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('auction_start')->nullable();
            $table->timestamp('auction_end')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            
            // Winner/Buyer
            $table->unsignedBigInteger('winner_id')->default(0);
            $table->decimal('final_price', 28, 8)->default(0);
            
            // Escrow Integration
            $table->unsignedBigInteger('escrow_id')->default(0);
            
            // Stats
            $table->integer('view_count')->default(0);
            $table->integer('watchlist_count')->default(0);
            
            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('category_id');
            $table->index('listing_category_id');
            $table->index('business_type');
            $table->index('sale_type');
            $table->index('status');
            $table->index('is_featured');
            $table->index('auction_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};

