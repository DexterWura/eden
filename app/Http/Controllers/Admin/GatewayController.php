<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProFeatureService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function index()
    {
        $setting = app(SettingService::class);
        $pro = app(ProFeatureService::class);
        $features = $pro->getFeatures();
        $prices = [];
        $isPro = [];
        foreach (array_keys($features) as $key) {
            $prices[$key] = $setting->get("pro_feature_{$key}_price", config("pro_features.default_prices.{$key}", 0));
            $val = $setting->get("pro_feature_{$key}_is_pro");
            $isPro[$key] = $val === null || $val === '' || in_array($val, [1, '1', true, 'true', 'on'], true);
        }
        return view('admin.gateways.index', [
            'paypal_client_id' => $setting->get('paypal_client_id'),
            'paypal_secret' => $setting->get('paypal_secret'),
            'paynow_integration_id' => $setting->get('paynow_integration_id'),
            'paynow_integration_key' => $setting->get('paynow_integration_key'),
            'features' => $features,
            'prices' => $prices,
            'isPro' => $isPro,
            'gateways' => $pro->getGateways(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'paypal_client_id' => 'nullable|string|max:255',
            'paypal_secret' => 'nullable|string|max:255',
            'paynow_integration_id' => 'nullable|string|max:255',
            'paynow_integration_key' => 'nullable|string|max:255',
        ]);

        $setting = app(SettingService::class);
        $setting->set('paypal_client_id', $validated['paypal_client_id'] ?? '');
        $setting->set('paypal_secret', $validated['paypal_secret'] ?? '');
        $setting->set('paynow_integration_id', $validated['paynow_integration_id'] ?? '');
        $setting->set('paynow_integration_key', $validated['paynow_integration_key'] ?? '');

        $features = array_keys(config('pro_features.features', []));
        foreach ($features as $key) {
            $setting->set("pro_feature_{$key}_is_pro", $request->boolean("pro_feature_{$key}_is_pro") ? '1' : '0');
            $priceKey = "pro_feature_{$key}_price";
            if ($request->has($priceKey)) {
                $price = $request->input($priceKey);
                $setting->set($priceKey, is_numeric($price) ? (float) $price : 0);
            }
        }

        return redirect()->route('admin.gateways.index')->with('success', 'Gateway and pro feature settings saved.');
    }
}
