<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\Subscriber;
use App\Models\User;

class ReportsController extends Controller
{
    public function index()
    {
        $totalStartups = Startup::count();
        $activeStartups = Startup::active()->count();
        $featuredStartups = Startup::featured()->count();
        $totalUsers = User::count();
        $totalSubscribers = Subscriber::count();
        $launchingToday = Startup::active()->launchingToday()->count();

        $startupsByCategory = Startup::active()
            ->selectRaw('category, count(*) as count')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        $recentStartups = Startup::query()->orderByDesc('created_at')->limit(10)->get();

        $content = view('eden.reports.index', [
            'totalStartups' => $totalStartups,
            'activeStartups' => $activeStartups,
            'featuredStartups' => $featuredStartups,
            'totalUsers' => $totalUsers,
            'totalSubscribers' => $totalSubscribers,
            'launchingToday' => $launchingToday,
            'startupsByCategory' => $startupsByCategory,
            'recentStartups' => $recentStartups,
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Reports', 'reports', $content));
    }

    private function dashboardVars(string $title, string $activeNav, string $content): array
    {
        return [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
        ];
    }
}
