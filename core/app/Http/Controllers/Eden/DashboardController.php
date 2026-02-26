<?php

namespace App\Http\Controllers\Eden;

use App\Models\ContactSubmission;
use App\Models\Startup;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Response;

class DashboardController extends EdenController
{
    private function siteName(): string
    {
        return function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
    }

    public function founderDashboard(): Response
    {
        $user = auth()->user();
        $myStartups = Startup::visibleToUser($user)->orderByDesc('updated_at')->get();
        $totalUpvotes = $myStartups->sum('upvotes');
        $primaryStartup = $myStartups->first();
        $siteName = $this->siteName();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Startup dashboard',
            'sidebar' => 'founder',
            'activeNav' => 'home',
            'dashboardLogo' => $siteName,
            'dashboardTopbar' => $primaryStartup ? '<a href="' . url('/startup/' . $primaryStartup->slug) . '" target="_blank" class="dash-account" style="text-decoration:none;">' . e($primaryStartup->name) . ' · Founder</a>' : '',
            'searchPlaceholder' => "Try searching 'upvotes this week'",
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'notifyPartial' => view('partials.notify')->render(),
            'content' => view('eden.founder-dashboard', [
                'myStartups' => $myStartups,
                'totalUpvotes' => $totalUpvotes,
            ])->render(),
        ]);
    }

    public function adminDashboard(): Response
    {
        $totalStartups = Startup::count();
        $activeStartups = Startup::active()->count();
        $launchingToday = Startup::active()->launchingToday()->count();
        $recentStartups = Startup::query()->orderByDesc('created_at')->limit(5)->get();
        $recentContactMessages = ContactSubmission::query()->orderByDesc('created_at')->limit(10)->get();
        $siteName = $this->siteName();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Admin dashboard',
            'sidebar' => 'admin',
            'dashboardLogo' => $siteName . ' Admin',
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
                'recentContactMessages' => $recentContactMessages,
            ])->render(),
        ]);
    }
}
