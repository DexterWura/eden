<?php

use App\Models\Frontend;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove subheading and status/hero_status from marketplace hero content; add trending_fallback.
     * Hero is always on—no on/off state stored.
     */
    public function up(): void
    {
        if (! Schema::hasTable('frontends')) {
            return;
        }

        $defaultFallback = 'SaaS, Blogs, Shopify, Youtube, Ads, Store';

        Frontend::where('data_keys', 'marketplace_hero.content')->get()->each(function (Frontend $row) use ($defaultFallback) {
            $dataValues = is_string($row->data_values)
                ? json_decode($row->data_values, true)
                : (array) $row->data_values;
            $dataValues = $dataValues ?? [];

            unset($dataValues['subheading'], $dataValues['status'], $dataValues['hero_status']);
            if (! isset($dataValues['trending_fallback']) || trim((string) $dataValues['trending_fallback']) === '') {
                $dataValues['trending_fallback'] = $defaultFallback;
            }

            $row->data_values = $dataValues;
            $row->save();
        });
    }

    public function down(): void
    {
        // Intentionally no-op: do not restore subheading.
    }
};
