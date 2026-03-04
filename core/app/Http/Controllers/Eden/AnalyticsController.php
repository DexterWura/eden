<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\StartupComment;
use App\Models\StartupRevenueEvent;
use App\Models\StartupUpvote;
use Illuminate\Http\Response;

class AnalyticsController extends EdenController
{
    public function index(): Response
    {
        $user = auth()->user();
        if (! $user->isPro()) {
            abort(403, 'Pro membership required to access analytics.');
        }

        $startupIds = Startup::visibleToUser($user)->pluck('id');

        if ($startupIds->isEmpty()) {
            return $this->renderAnalytics($user, [
                'startups' => collect(),
                'totalViews' => 0,
                'totalClicks' => 0,
                'totalUpvotes' => 0,
                'totalComments' => 0,
                'totalRevenue' => 0,
                'totalMrr' => 0,
                'revenueByDay' => [],
                'upvotesByDay' => [],
                'commentsByDay' => [],
                'startupMetrics' => [],
            ]);
        }

        $startups = Startup::visibleToUser($user)->orderBy('name')->get();

        $totalViews = $startups->sum('views');
        $totalClicks = $startups->sum('clicks');
        $totalUpvotes = $startups->sum('upvotes');
        $totalRevenue = (float) $startups->sum('revenue');
        $totalMrr = (float) $startups->sum('mrr');
        $totalComments = StartupComment::whereIn('startup_id', $startupIds)->count();

        $days = 60;
        $startDate = now()->subDays($days);

        $revenueByDay = StartupRevenueEvent::whereIn('startup_id', $startupIds)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $upvotesByDay = StartupUpvote::whereIn('startup_id', $startupIds)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $commentsByDay = StartupComment::whereIn('startup_id', $startupIds)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $startupMetrics = $startups->map(fn ($s) => [
            'name' => $s->name,
            'slug' => $s->slug,
            'views' => (int) $s->views,
            'clicks' => (int) $s->clicks,
            'upvotes' => (int) $s->upvotes,
            'comments' => $s->comments()->count(),
            'revenue' => (float) $s->revenue,
            'mrr' => (float) $s->mrr,
        ])->toArray();

        return $this->renderAnalytics($user, [
            'startups' => $startups,
            'totalViews' => $totalViews,
            'totalClicks' => $totalClicks,
            'totalUpvotes' => $totalUpvotes,
            'totalComments' => $totalComments,
            'totalRevenue' => $totalRevenue,
            'totalMrr' => $totalMrr,
            'revenueByDay' => $revenueByDay,
            'upvotesByDay' => $upvotesByDay,
            'commentsByDay' => $commentsByDay,
            'startupMetrics' => $startupMetrics,
            'days' => $days,
        ]);
    }

    private function renderAnalytics($user, array $data): Response
    {
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $primaryStartup = $data['startups']->first();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Analytics',
            'sidebar' => 'founder',
            'activeNav' => 'analytics',
            'dashboardLogo' => $siteName,
            'dashboardTopbar' => $primaryStartup ? '<a href="' . url('/startup/' . $primaryStartup->slug) . '" target="_blank" class="dash-account" style="text-decoration:none;">' . e($primaryStartup->name) . ' · Founder</a>' : '',
            'searchPlaceholder' => "Try searching 'revenue this month'",
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'notifyPartial' => view('partials.notify')->render(),
            'scriptDeps' => '<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>',
            'content' => view('eden.founder.analytics', $data)->render(),
        ]);
    }
}
