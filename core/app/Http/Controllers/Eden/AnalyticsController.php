<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\StartupComment;
use App\Models\StartupRevenueEvent;
use App\Models\StartupUpvote;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function exportCsv(): StreamedResponse|Response
    {
        $user = auth()->user();
        if (! $user->isPro()) {
            abort(403, 'Pro membership required to export analytics.');
        }

        $data = $this->gatherAnalyticsData($user);
        $startupMetrics = $data['startupMetrics'];
        $days = $data['days'];
        $revenueByDay = $data['revenueByDay'];
        $upvotesByDay = $data['upvotesByDay'];
        $commentsByDay = $data['commentsByDay'];

        $filename = 'eden-analytics-' . Date::now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($startupMetrics, $days, $revenueByDay, $upvotesByDay, $commentsByDay) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Eden Analytics Export', Date::now()->toDateTimeString()]);
            fputcsv($out, []);
            fputcsv($out, ['Summary', 'Total']);
            fputcsv($out, ['Total views', $startupMetrics ? array_sum(array_column($startupMetrics, 'views')) : 0]);
            fputcsv($out, ['Total clicks', $startupMetrics ? array_sum(array_column($startupMetrics, 'clicks')) : 0]);
            fputcsv($out, ['Total upvotes', $startupMetrics ? array_sum(array_column($startupMetrics, 'upvotes')) : 0]);
            fputcsv($out, ['Total comments', $startupMetrics ? array_sum(array_column($startupMetrics, 'comments')) : 0]);
            fputcsv($out, ['Total revenue', $startupMetrics ? array_sum(array_column($startupMetrics, 'revenue')) : 0]);
            fputcsv($out, ['Total MRR', $startupMetrics ? array_sum(array_column($startupMetrics, 'mrr')) : 0]);
            fputcsv($out, []);
            fputcsv($out, ['Per-startup metrics']);
            fputcsv($out, ['Startup', 'Views', 'Clicks', 'Upvotes', 'Comments', 'Revenue', 'MRR']);
            foreach ($startupMetrics as $m) {
                fputcsv($out, [$m['name'], $m['views'], $m['clicks'], $m['upvotes'], $m['comments'], $m['revenue'], $m['mrr']]);
            }
            fputcsv($out, []);
            $dates = collect();
            for ($i = $days - 1; $i >= 0; $i--) {
                $dates->push(now()->subDays($i)->format('Y-m-d'));
            }
            fputcsv($out, ['Daily time series (last ' . $days . ' days)']);
            fputcsv($out, ['Date', 'Revenue', 'Upvotes', 'Comments']);
            foreach ($dates as $d) {
                fputcsv($out, [
                    $d,
                    $revenueByDay[$d] ?? 0,
                    $upvotesByDay[$d] ?? 0,
                    $commentsByDay[$d] ?? 0,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(): Response|StreamedResponse
    {
        $user = auth()->user();
        if (! $user->isPro()) {
            abort(403, 'Pro membership required to export analytics.');
        }

        $data = $this->gatherAnalyticsData($user);
        $data['exportedAt'] = Date::now()->toDateTimeString();
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $data['siteName'] = $siteName;

        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            abort(503, 'PDF export is not available.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('eden.founder.analytics-export-pdf', $data);
        $filename = 'eden-analytics-' . Date::now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function gatherAnalyticsData($user): array
    {
        $startupIds = Startup::visibleToUser($user)->pluck('id');
        $startups = Startup::visibleToUser($user)->orderBy('name')->get();

        if ($startupIds->isEmpty()) {
            return [
                'startupMetrics' => [],
                'totalViews' => 0,
                'totalClicks' => 0,
                'totalUpvotes' => 0,
                'totalComments' => 0,
                'totalRevenue' => 0,
                'totalMrr' => 0,
                'revenueByDay' => [],
                'upvotesByDay' => [],
                'commentsByDay' => [],
                'days' => 60,
            ];
        }

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

        return [
            'startupMetrics' => $startupMetrics,
            'totalViews' => array_sum(array_column($startupMetrics, 'views')),
            'totalClicks' => array_sum(array_column($startupMetrics, 'clicks')),
            'totalUpvotes' => array_sum(array_column($startupMetrics, 'upvotes')),
            'totalComments' => array_sum(array_column($startupMetrics, 'comments')),
            'totalRevenue' => array_sum(array_column($startupMetrics, 'revenue')),
            'totalMrr' => array_sum(array_column($startupMetrics, 'mrr')),
            'revenueByDay' => $revenueByDay,
            'upvotesByDay' => $upvotesByDay,
            'commentsByDay' => $commentsByDay,
            'days' => $days,
        ];
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
