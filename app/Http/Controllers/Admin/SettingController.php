<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = app(SettingService::class);
        return view('admin.settings.index', [
            'site_name' => $setting->get('site_name', config('app.name')),
            'site_logo' => $setting->get('site_logo'),
            'app_url' => $setting->get('app_url', config('app.url')),
            'timezone' => $setting->get('timezone', config('app.timezone')),
            'adsense_client_id' => $setting->get('adsense_client_id'),
            'theme' => $setting->get('theme', config('themes.default', 'basic')),
            'themes' => config('themes.themes', ['basic' => 'Basic']),
        ]);
    }

    public function update(Request $request)
    {
        $themes = array_keys(config('themes.themes', ['basic' => 'Basic']));
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'timezone' => 'required|string|max:50',
            'adsense_client_id' => 'nullable|string|max:255',
            'theme' => 'required|string|in:' . implode(',', $themes),
            'site_logo' => 'nullable|image|max:2048',
        ]);

        $setting = app(SettingService::class);
        $setting->set('site_name', $validated['site_name']);
        $setting->set('app_url', $validated['app_url']);
        $setting->set('timezone', $validated['timezone']);
        $setting->set('adsense_client_id', $validated['adsense_client_id'] ?? '');
        $setting->set('theme', $validated['theme']);

        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('logos', 'public');
            $setting->set('site_logo', Storage::url($path));
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved.');
    }
}
