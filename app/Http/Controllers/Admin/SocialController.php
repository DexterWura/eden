<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SocialController extends Controller
{
    public function index(SettingService $setting): View
    {
        $platforms = config('social_platforms.platforms', []);
        $credentials = [];
        foreach (array_keys($platforms) as $key) {
            $credentials[$key] = [
                'app_id' => $setting->get("social_{$key}_app_id"),
                'app_secret' => $setting->get("social_{$key}_app_secret"),
            ];
        }

        return view('admin.social.index', [
            'platforms' => $platforms,
            'credentials' => $credentials,
        ]);
    }

    public function update(Request $request, SettingService $setting): RedirectResponse
    {
        $platforms = array_keys(config('social_platforms.platforms', []));
        $rules = [];
        foreach ($platforms as $key) {
            $rules["social_{$key}_app_id"] = 'nullable|string|max:255';
            $rules["social_{$key}_app_secret"] = 'nullable|string|max:255';
        }
        $validated = $request->validate($rules);

        foreach ($platforms as $key) {
            $setting->set("social_{$key}_app_id", $validated["social_{$key}_app_id"] ?? '');
            if (array_key_exists("social_{$key}_app_secret", $validated) && $validated["social_{$key}_app_secret"] !== '') {
                $setting->set("social_{$key}_app_secret", $validated["social_{$key}_app_secret"]);
            }
        }

        return redirect()->route('admin.social.index')->with('success', 'Social app credentials saved.');
    }
}
