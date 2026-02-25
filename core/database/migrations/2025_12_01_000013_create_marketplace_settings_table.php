<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $settings = [
            ['key' => 'allow_auctions', 'value' => '1'],
            ['key' => 'allow_fixed_price', 'value' => '1'],
            ['key' => 'allow_domain_listings', 'value' => '1'],
            ['key' => 'allow_website_listings', 'value' => '1'],
            ['key' => 'allow_social_media_listings', 'value' => '1'],
            ['key' => 'allow_mobile_app_listings', 'value' => '1'],
            ['key' => 'allow_desktop_app_listings', 'value' => '1'],
            ['key' => 'max_auction_days', 'value' => '30'],
            ['key' => 'min_auction_days', 'value' => '1'],
            ['key' => 'require_domain_verification', 'value' => '1'],
            ['key' => 'require_website_verification', 'value' => '1'],
            ['key' => 'require_social_media_verification', 'value' => '1'],
            ['key' => 'domain_verification_methods', 'value' => '["txt_file","dns_record"]'],
            ['key' => 'listing_approval_required', 'value' => '1'],
            ['key' => 'max_images_per_listing', 'value' => '10'],
            ['key' => 'min_listing_description', 'value' => '100'],
            ['key' => 'featured_listing_fee', 'value' => '0'],
            ['key' => 'listing_fee_percentage', 'value' => '0'],
            ['key' => 'escrow_fee_percentage', 'value' => '5'],
            ['key' => 'auto_extend_auction_minutes', 'value' => '10'],
            ['key' => 'bid_extension_threshold_minutes', 'value' => '5'],
        ];

        foreach ($settings as $setting) {
            DB::table('marketplace_settings')->insert([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_settings');
    }
};

