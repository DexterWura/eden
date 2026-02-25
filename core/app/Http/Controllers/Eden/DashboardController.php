<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Response;

class DashboardController extends EdenController
{
    public function founderDashboard(): Response
    {
        return response()->view('eden.layout-dashboard', [
            'title' => 'Startup dashboard',
            'sidebar' => 'founder',
            'dashboardLogo' => 'Eden',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Switch account">Nexus Pay · Founder</button>',
            'searchPlaceholder' => "Try searching 'upvotes this week'",
            'avatarTitle' => 'Account',
            'avatarLetter' => 'S',
            'content' => view('eden.founder-dashboard')->render(),
        ]);
    }

    public function adminDashboard(): Response
    {
        $totalStartups = Startup::count();
        $activeStartups = Startup::active()->count();
        $launchingToday = Startup::active()->launchingToday()->count();
        $recentStartups = Startup::query()->orderByDesc('created_at')->limit(5)->get();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Admin dashboard',
            'sidebar' => 'admin',
            'dashboardLogo' => 'Eden Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All startups</button>',
            'searchPlaceholder' => "Try searching 'startups by category'",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => view('eden.admin-dashboard', [
                'totalStartups' => $totalStartups,
                'activeStartups' => $activeStartups,
                'launchingToday' => $launchingToday,
                'totalUsers' => User::count(),
                'totalSubscribers' => Subscriber::count(),
                'recentStartups' => $recentStartups,
            ])->render(),
        ]);
    }
}
