<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class ProFeatureService
{
    public function getFeatures(): array
    {
        return config('pro_features.features', []);
    }

    /** Features that require payment (admin can mark each feature as pro or free). */
    public function getProFeatures(): array
    {
        $all = $this->getFeatures();
        $setting = App::make(SettingService::class);
        $pro = [];
        foreach ($all as $key => $info) {
            if ($this->isProFeature($key, $setting)) {
                $pro[$key] = $info;
            }
        }
        return $pro;
    }

    /** Whether this feature is paid (pro). When false, everyone has access. */
    public function isProFeature(string $featureKey, ?SettingService $setting = null): bool
    {
        $setting = $setting ?? App::make(SettingService::class);
        $value = $setting->get("pro_feature_{$featureKey}_is_pro");
        if ($value === null || $value === '') {
            return true;
        }
        return in_array($value, [1, '1', true, 'true', 'on'], true);
    }

    public function getPrice(string $featureKey, string $currency = 'USD'): float
    {
        $setting = App::make(SettingService::class);
        $key = "pro_feature_{$featureKey}_price";
        $stored = $setting->get($key);
        if ($stored !== null && is_numeric($stored)) {
            return (float) $stored;
        }
        $defaults = config('pro_features.default_prices', []);
        return (float) ($defaults[$featureKey] ?? 0);
    }

    public function getGateways(): array
    {
        return config('pro_features.gateways', []);
    }

    public function getCurrencies(): array
    {
        return config('pro_features.currencies', []);
    }
}
