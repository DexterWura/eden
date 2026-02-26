<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\StartupUpvote;
use Illuminate\Http\Response;

class UpvotesController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $startups = $user->startups()->get();
        $totalUpvotes = $startups->sum('upvotes');
        $recentUpvotes = StartupUpvote::whereIn('startup_id', $startups->pluck('id'))
            ->with('user:id,name', 'startup:id,name,slug')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $content = view('eden.founder.upvotes', [
            'startups' => $startups,
            'totalUpvotes' => $totalUpvotes,
            'recentUpvotes' => $recentUpvotes,
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Upvotes',
            'sidebar' => 'founder',
            'activeNav' => 'upvotes',
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'content' => $content,
        ]);
    }
}
