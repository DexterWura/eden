<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class StartupWebsiteHealthController extends Controller
{
    public function index(Request $request)
    {
        $query = Startup::query()->orderBy('name');
        $filter = $request->get('filter', '');
        if ($filter === 'with-website') {
            $query->whereNotNull('website')->where('website', '!=', '');
        } elseif ($filter === 'active') {
            $query->where('status', Startup::STATUS_ACTIVE);
        } elseif ($filter === 'dormant') {
            $query->where('status', Startup::STATUS_DORMANT);
        }
        $startups = $query->paginate(25)->withQueryString();

        $content = view('eden.startup-website-health.index', [
            'startups' => $startups,
            'filter' => $filter,
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'App website health',
            'sidebar' => 'admin',
            'activeNav' => 'startup-websites',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function runCheck(Request $request): RedirectResponse
    {
        $force = $request->boolean('force');
        Artisan::call('startups:check-websites', ['--force' => $force]);
        $message = $force ? 'Website check completed (all apps with a website).' : 'Website check completed (apps due for check).';
        return redirect()->route('admin.startup-websites.index')
            ->with('notify', [['success', $message]]);
    }
}
