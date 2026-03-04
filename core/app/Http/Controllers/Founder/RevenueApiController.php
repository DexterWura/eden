<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\StartupRevenueApiKey;
use App\Models\StartupRevenueIntegration;
use App\Services\Revenue\RevenueSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
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
        $integrationsByStartup = StartupRevenueIntegration::whereIn('startup_id', $startups->pluck('id'))
            ->get()
            ->groupBy('startup_id');

        $content = view('eden.founder.revenue-api', [
            'startups' => $startups,
            'keysByStartup' => $keysByStartup,
            'integrationsByStartup' => $integrationsByStartup,
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

    public function connectIntegration(Request $request, Startup $startup): RedirectResponse
    {
        if (! $startup->userCanManage(auth()->user())) {
            abort(403);
        }

        $validated = Validator::make($request->all(), [
            'gateway' => ['required', 'string', 'in:stripe,polar,lemonsqueezy'],
            'api_key' => ['required', 'string', 'min:10'],
        ])->validate();

        $existing = StartupRevenueIntegration::where('startup_id', $startup->id)
            ->where('gateway', $validated['gateway'])
            ->first();

        if ($existing) {
            $existing->setCredentials(['api_key' => trim($validated['api_key'])]);
            return redirect()->route('founder.revenue-api')->with('notify', [['success', 'Integration updated.']]);
        }

        StartupRevenueIntegration::create([
            'startup_id' => $startup->id,
            'gateway' => $validated['gateway'],
            'credentials' => Crypt::encryptString(json_encode(['api_key' => trim($validated['api_key'])])),
        ]);

        return redirect()->route('founder.revenue-api')->with('notify', [['success', 'Integration connected. Revenue will sync automatically.']]);
    }

    public function disconnectIntegration(Startup $startup, string $gateway): RedirectResponse
    {
        if (! $startup->userCanManage(auth()->user())) {
            abort(403);
        }

        if (! in_array($gateway, ['stripe', 'polar', 'lemonsqueezy'], true)) {
            abort(404);
        }

        StartupRevenueIntegration::where('startup_id', $startup->id)->where('gateway', $gateway)->delete();

        return redirect()->route('founder.revenue-api')->with('notify', [['success', 'Integration disconnected.']]);
    }

    public function syncNow(Startup $startup): RedirectResponse
    {
        if (! $startup->userCanManage(auth()->user())) {
            abort(403);
        }

        $integrations = StartupRevenueIntegration::where('startup_id', $startup->id)->get();
        $syncService = app(RevenueSyncService::class);
        foreach ($integrations as $int) {
            $syncService->sync($int);
        }

        return redirect()->route('founder.revenue-api')->with('notify', [['success', 'Revenue synced.']]);
    }
}
