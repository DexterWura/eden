<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $general = function_exists('gs') ? gs() : null;
        $seo = $general ? (object) [
            'meta_keywords' => $general->meta_keywords ?? '',
            'meta_description' => $general->meta_description ?? '',
            'social_description' => $general->social_description ?? '',
            'seo_image' => $general->seo_image ?? '',
        ] : (object) ['meta_keywords' => '', 'meta_description' => '', 'social_description' => '', 'seo_image' => ''];
        $content = view('eden.settings.index', compact('seo'))->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Settings',
            'sidebar' => 'admin',
            'activeNav' => 'settings',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function updateSeo(Request $request): RedirectResponse
    {
        $request->validate([
            'meta_keywords' => 'nullable|string|max:2000',
            'meta_description' => 'nullable|string|max:1000',
            'social_description' => 'nullable|string|max:1000',
            'seo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $general = function_exists('gs') ? gs() : null;
        if (!$general || !$general->exists) {
            return redirect()->route('admin.settings.index')->with('notify', [['error', 'General settings not found.']]);
        }

        $general->meta_keywords = $request->filled('meta_keywords') ? $request->meta_keywords : null;
        $general->meta_description = $request->filled('meta_description') ? $request->meta_description : null;
        $general->social_description = $request->filled('social_description') ? $request->social_description : null;

        if ($request->hasFile('seo_image')) {
            $dir = public_path('images/seo');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $name = 'og-' . time() . '.' . $request->seo_image->getClientOriginalExtension();
            $request->seo_image->move($dir, $name);
            $general->seo_image = 'images/seo/' . $name;
        }

        $general->save();
        Cache::forget('GeneralSetting');

        return redirect()->route('admin.settings.index')->with('notify', [['success', 'SEO settings saved.']]);
    }
}
