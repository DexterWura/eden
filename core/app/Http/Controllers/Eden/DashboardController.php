<?php

namespace App\Http\Controllers\Eden;

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
        return response()->view('eden.layout-dashboard', [
            'title' => 'Admin dashboard',
            'sidebar' => 'admin',
            'dashboardLogo' => 'Eden Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All startups</button>',
            'searchPlaceholder' => "Try searching 'startups by category'",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => view('eden.admin-dashboard')->render(),
        ]);
    }
}
