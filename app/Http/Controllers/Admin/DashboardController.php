<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Startup;

class DashboardController extends Controller
{
    public function __invoke()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('admin.startups.index');
        }
        $totalStartups = Startup::count();
        $pendingSubmissions = Startup::whereNull('approved_at')->whereNotNull('submitted_at')->count();
        $claimed = Startup::whereNotNull('user_id')->count();
        $unclaimed = $totalStartups - $claimed;
        $recentSubmissions = Startup::whereNull('approved_at')->whereNotNull('submitted_at')->latest('submitted_at')->limit(10)->get();
        $mostViewed = Startup::approved()->orderByDesc('view_count')->limit(5)->get();
        $topMrr = Startup::approved()->where('mrr', '>', 0)->orderByDesc('mrr')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalStartups', 'pendingSubmissions', 'claimed', 'unclaimed', 'recentSubmissions', 'mostViewed', 'topMrr'
        ));
    }
}
