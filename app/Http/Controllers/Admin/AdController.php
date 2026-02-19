<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Services\SettingService;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::orderBy('slot')->orderBy('id')->get()->groupBy('slot');
        $setting = app(SettingService::class);
        $adsenseClientId = $setting->get('adsense_client_id');
        return view('admin.ads.index', compact('ads', 'adsenseClientId'));
    }

    public function create()
    {
        return view('admin.ads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slot' => 'required|in:above_fold,in_feed,sidebar,in_content',
            'type' => 'required|in:adsense,zimadsense,custom',
            'name' => 'nullable|string|max:255',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'content' => 'nullable|string',
            'adsense_client' => 'nullable|string|max:255',
            'adsense_slot' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        Ad::create($validated);
        return redirect()->route('admin.ads.index')->with('success', 'Ad created.');
    }

    public function edit(Ad $ad)
    {
        return view('admin.ads.edit', compact('ad'));
    }

    public function update(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'slot' => 'required|in:above_fold,in_feed,sidebar,in_content',
            'type' => 'required|in:adsense,zimadsense,custom',
            'name' => 'nullable|string|max:255',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'content' => 'nullable|string',
            'adsense_client' => 'nullable|string|max:255',
            'adsense_slot' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $ad->update($validated);
        return redirect()->route('admin.ads.index')->with('success', 'Ad updated.');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();
        return redirect()->route('admin.ads.index')->with('success', 'Ad deleted.');
    }
}
