<?php

use App\Models\Frontend;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seed default marketplace hero content so production keeps the current look
     * (title "#1 Platform to Buy & Sell"). Hero has no on/off status—it is always active.
     * Only inserts when no row exists; does not overwrite existing admin content.
     */
    public function up(): void
    {
        if (! Schema::hasTable('frontends')) {
            return;
        }

        Frontend::firstOrCreate(
            ['data_keys' => 'marketplace_hero.content'],
            [
                'data_values' => [
                    'heading'           => '#1 Platform to Buy & Sell',
                    'trending_fallback' => 'SaaS, Blogs, Shopify, Youtube, Ads, Store',
                ],
            ]
        );
    }

    /**
     * Reverse: we do not delete the row so existing sites keep their hero content.
     */
    public function down(): void
    {
        // Intentionally no-op: avoid removing admin-configured hero content.
    }
};
