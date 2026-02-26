<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\StartupRevenueApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RevenueApiController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $startups = Startup::visibleToUser($user)->orderByDesc('updated_at')->get();
        $keysByStartup = StartupRevenueApiKey::whereIn('startup_id', $startups->pluck('id'))
            ->get()
            ->keyBy('startup_id');

        $content = view('eden.founder.revenue-api', [
            'startups' => $startups,
            'keysByStartup' => $keysByStartup,
            'apiBaseUrl' => url('/api/eden/v1'),
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Revenue API',
            'sidebar' => 'founder',
            'activeNav' => 'revenue-api',
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'content' => $content,
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }


    public function createKey(Request $request, Startup $startup): RedirectResponse
    {
        if (! $startup->userCanManage(auth()->user())) {
            abort(403, 'You cannot manage this startup.');
        }

        $existing = StartupRevenueApiKey::where('startup_id', $startup->id)->first();
        if ($existing) {
            return redirect()->route('founder.revenue-api')->with('notify', [['error', 'This startup already has an API key. Use Regenerate to replace it.']]);
        }

        $token = StartupRevenueApiKey::generateToken();
        StartupRevenueApiKey::create([
            'startup_id' => $startup->id,
            'token_hash' => StartupRevenueApiKey::hashToken($token),
            'name' => 'Default',
        ]);

        return redirect()->route('founder.revenue-api')->with('notify', [['success', 'API key created. Copy it now — it will not be shown again.']])->with('revealed_api_key', $token)->with('revealed_api_key_startup_id', $startup->id);
    }

    public function regenerateKey(Request $request, Startup $startup): RedirectResponse
    {
        if (! $startup->userCanManage(auth()->user())) {
            abort(403, 'You cannot manage this startup.');
        }

        StartupRevenueApiKey::where('startup_id', $startup->id)->delete();
        $token = StartupRevenueApiKey::generateToken();
        StartupRevenueApiKey::create([
            'startup_id' => $startup->id,
            'token_hash' => StartupRevenueApiKey::hashToken($token),
            'name' => 'Default',
        ]);

        return redirect()->route('founder.revenue-api')->with('notify', [['success', 'API key regenerated. Copy the new key — it will not be shown again.']])->with('revealed_api_key', $token)->with('revealed_api_key_startup_id', $startup->id);
    }
}
