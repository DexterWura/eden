<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarketplaceSetting extends Model
{
    protected $guarded = ['id'];

    /**
     * Get a marketplace setting value by key
     */
    public static function getValue($key, $default = null)
    {
        return Cache::remember('marketplace_setting_' . $key, 3600, function () use ($key, $default) {
            try {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            } catch (\Exception $e) {
                return $default;
            }
        });
    }

    /**
     * Set a marketplace setting value
     */
    public static function setValue($key, $value)
    {
        try {
            self::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
            Cache::forget('marketplace_setting_' . $key);
            Cache::forget('marketplace_settings_all');
        } catch (\Exception $e) {
            // Database not available, just update cache temporarily
            // This allows the system to work even without database
            Cache::put('marketplace_setting_' . $key, $value, 3600);
        }
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings()
    {
        return Cache::remember('marketplace_settings_all', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear all marketplace settings cache
     */
    public static function clearCache()
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget('marketplace_setting_' . $setting->key);
        }
        Cache::forget('marketplace_settings_all');
    }

    // Convenience methods for common settings
    public static function allowAuctions()
    {
        return (bool) self::getValue('allow_auctions', true);
    }

    public static function allowFixedPrice()
    {
        return (bool) self::getValue('allow_fixed_price', true);
    }

    public static function allowBusinessType($type)
    {
        $keyMap = [
            'domain' => 'allow_domain_listings',
            'website' => 'allow_website_listings',
            'social_media_account' => 'allow_social_media_listings',
            'mobile_app' => 'allow_mobile_app_listings',
            'desktop_app' => 'allow_desktop_app_listings',
        ];

        $key = $keyMap[$type] ?? null;
        return $key ? (bool) self::getValue($key, true) : true;
    }

    public static function maxAuctionDays()
    {
        return (int) self::getValue('max_auction_days', 30);
    }

    public static function minAuctionDays()
    {
        return (int) self::getValue('min_auction_days', 1);
    }


    public static function requireListingApproval()
    {
        return (bool) self::getValue('listing_approval_required', true);
    }

    public static function maxImagesPerListing()
    {
        return (int) self::getValue('max_images_per_listing', 10);
    }

    public static function minListingDescription()
    {
        return (int) self::getValue('min_listing_description', 100);
    }

    public static function autoExtendAuctionMinutes()
    {
        return (int) self::getValue('auto_extend_auction_minutes', 10);
    }

    public static function bidExtensionThresholdMinutes()
    {
        return (int) self::getValue('bid_extension_threshold_minutes', 5);
    }
}

