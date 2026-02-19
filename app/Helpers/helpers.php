<?php

use App\Models\Ad;
use App\Services\SettingService;

if (! function_exists('ad_slot')) {
    function ad_slot(string $slot): string
    {
        $now = now();
        $ads = Ad::where('slot', $slot)->where('is_active', true)->orderBy('id')->get();

        foreach ($ads as $ad) {
            if ($ad->expires_at && $ad->expires_at->isBefore($now)) {
                continue;
            }

            if ($ad->type === Ad::TYPE_ADSENSE) {
                $client = $ad->adsense_client ?: app(SettingService::class)->get('adsense_client_id');
                $slotId = $ad->adsense_slot;
                if ($client && $slotId) {
                    return '<ins class="adsbygoogle" style="display:block" data-ad-client="' . e($client) . '" data-ad-slot="' . e($slotId) . '" data-ad-format="auto"></ins><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';
                }
                continue;
            }

            if ($ad->type === Ad::TYPE_ZIMADSENSE || $ad->type === Ad::TYPE_CUSTOM) {
                $style = ($ad->width && $ad->height) ? " width:{$ad->width}px;height:{$ad->height}px;" : '';
                return '<div class="eden-ad-slot" style="' . $style . '">' . $ad->content . '</div>';
            }
        }

        $client = app(SettingService::class)->get('adsense_client_id');
        $fallbackSlot = Ad::where('slot', $slot)->where('type', Ad::TYPE_ADSENSE)->where('is_active', true)->first();
        $slotId = $fallbackSlot?->adsense_slot;
        if ($client && $slotId) {
            return '<ins class="adsbygoogle" style="display:block" data-ad-client="' . e($client) . '" data-ad-slot="' . e($slotId) . '" data-ad-format="auto"></ins><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';
        }
        if ($client) {
            return '<ins class="adsbygoogle" style="display:block" data-ad-client="' . e($client) . '" data-ad-format="auto"></ins><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';
        }

        return '';
    }
}
