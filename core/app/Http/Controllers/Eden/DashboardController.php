<?php

namespace App\Http\Controllers\Eden;

use App\Models\ContactSubmission;
use App\Models\AdSpot;
use App\Models\ScheduledTask;
use App\Models\Startup;
use App\Models\StartupReport;
use App\Models\Subscriber;
use App\Models\StartupRevenueEvent;
use App\Models\User;
use App\Services\FounderDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class DashboardController extends EdenController
{
    private function siteName(): string
    {
        return function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
    }

    public function founderDashboard(FounderDashboardService $dashboardService): Response
    {
        $user = auth()->user();
        $dashboard = $dashboardService->forFounder($user);
        $primaryStartup = $dashboard['myStartups']->first();
        $siteName = $this->siteName();

        return response()->view('eden.layout-dashboard', [
            'title' => 'App dashboard',
            'sidebar' => 'founder',
            'activeNav' => 'home',
            'dashboardLogo' => $siteName,
            'dashboardTopbar' => $primaryStartup ? '<a href="' . url('/startup/' . $primaryStartup->slug) . '" target="_blank" class="dash-account" style="text-decoration:none;">' . e($primaryStartup->name) . ' · Founder</a>' : '',
            'searchPlaceholder' => "Try searching 'upvotes this week'",
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'founderNavStatus' => $dashboard['founderNavStatus'],
            'notifyPartial' => view('partials.notify')->render(),
            'content' => view('eden.founder-dashboard', $dashboard)->render(),
        ]);
    }

    public function requestHeroFeature(Startup $startup): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->isPro()) {
            abort(403, 'Pro membership required to request hero featuring.');
        }

        if ((int) $startup->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($startup->featured_on_hero) {
            return redirect()->route('founder.dashboard')
                ->with('notify', [['info', $startup->name . ' is already featured on hero.']]);
        }

        if ($startup->hero_request_status === 'pending') {
            return redirect()->route('founder.dashboard')
                ->with('notify', [['info', 'Your request for ' . $startup->name . ' is already pending.']]);
        }

        if (! $startup->hasFounderWithLinkedin()) {
            return redirect()->route('founder.dashboard')
                ->with('notify', [['error', 'Please add a LinkedIn link to at least one founder on ' . $startup->name . ' before requesting.']]);
        }

        $startup->update(['hero_request_status' => 'pending']);

        return redirect()->route('founder.dashboard')
            ->with('notify', [['success', 'Your request to feature ' . $startup->name . ' on the hero section has been submitted.']]);
    }

    public function approveHeroRequest(Startup $startup): RedirectResponse
    {
        $startup->update([
            'hero_request_status' => 'approved',
            'featured_on_hero' => true,
        ]);

        if ($startup->user_id) {
            $user = User::find($startup->user_id);
            if ($user) {
                \DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'App\\Notifications\\HeroRequestApproved',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => 'Featured on hero!',
                        'message' => $startup->name . ' is now featured on the homepage hero section.',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('notify', [['success', $startup->name . ' is now featured on the hero section.']]);
    }

    public function rejectHeroRequest(Startup $startup): RedirectResponse
    {
        $startup->update(['hero_request_status' => 'rejected']);

        if ($startup->user_id) {
            $user = User::find($startup->user_id);
            if ($user) {
                \DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'App\\Notifications\\HeroRequestRejected',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => 'Hero feature request declined',
                        'message' => 'Your request to feature ' . $startup->name . ' on the homepage hero section was not approved at this time.',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('notify', [['success', 'Hero request for ' . $startup->name . ' has been declined.']]);
    }

    public function adminDashboard(): Response
    {
        $admin = auth()->guard('admin')->user();
        $canManageStartups = $admin?->hasModule('startups') ?? false;
        $canManageUsers = $admin?->hasModule('users') ?? false;
        $canManageSubscribers = $admin?->hasModule('subscribers') ?? false;
        $canManagePayments = $admin?->hasModule('payments') ?? false;
        $canManageMessages = $admin?->hasModule('messages') ?? false;
        $canManageReports = $admin?->hasModule('reports') ?? false;
        $canManageWebsites = $admin?->hasModule('website_health') ?? false;
        $canManageTasks = $admin?->hasModule('scheduled_tasks') ?? false;
        $canManageAds = $admin?->hasModule('advertising') ?? false;
        $totalStartups = $canManageStartups ? Startup::count() : null;
        $activeStartups = $canManageStartups ? Startup::active()->count() : null;
        $launchingToday = $canManageStartups ? Startup::active()->launchingToday()->count() : null;

        $pendingStartups = $canManageStartups
            ? Startup::where('status', Startup::STATUS_PENDING)->oldest()->limit(8)->get()
            : collect();
        $heroRequests = $canManageStartups
            ? Startup::where('hero_request_status', 'pending')->where('featured_on_hero', false)->oldest('updated_at')->limit(8)->get()
            : collect();
        $pendingReports = $canManageReports
            ? StartupReport::with('startup')->where('status', StartupReport::STATUS_PENDING)->oldest()->limit(8)->get()
            : collect();
        $unreadMessages = $canManageMessages
            ? ContactSubmission::query()
                ->when($admin?->last_saw_contact_messages_at, fn ($query, $seenAt) => $query->where('created_at', '>', $seenAt))
                ->latest()
                ->limit(8)
                ->get()
            : collect();
        $failedWebsiteChecks = $canManageWebsites
            ? Startup::where('website_is_reachable', false)->where('website_consecutive_failures', '>', 0)
                ->orderByDesc('website_consecutive_failures')->limit(8)->get()
            : collect();
        $failedTasks = $canManageTasks
            ? ScheduledTask::where('last_status', ScheduledTask::STATUS_FAILED)->orderByDesc('last_run_at')->limit(8)->get()
            : collect();
        $pendingAds = $canManageAds
            ? AdSpot::where('status', AdSpot::STATUS_PENDING)->oldest()->limit(8)->get()
            : collect();
        $siteName = $this->siteName();

        $days = 60;
        $startDate = now()->subDays($days);

        $revenueByDay = $canManagePayments
            ? StartupRevenueEvent::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray()
            : [];

        $usersByDay = $canManageUsers
            ? User::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray()
            : [];

        return response()->view('eden.layout-dashboard', [
            'title' => 'Admin dashboard',
            'sidebar' => 'admin',
            'activeNav' => 'home',
            'dashboardLogo' => $siteName . ' Admin',
            'dashboardTopbar' => $canManageStartups
                ? '<a class="dash-account" href="' . e(route('admin.startups.index')) . '">All apps</a>'
                : '<span class="dash-account">Command center</span>',
            'searchPlaceholder' => "Try searching 'apps by category'",
            'avatarTitle' => $admin?->name ?? 'Admin',
            'avatarLetter' => strtoupper(mb_substr($admin?->name ?? 'A', 0, 1)),
            'scriptDeps' => '<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>',
            'notifyPartial' => view('partials.notify')->render(),
            'content' => view('eden.admin-dashboard', [
                'totalStartups' => $totalStartups,
                'activeStartups' => $activeStartups,
                'launchingToday' => $launchingToday,
                'totalUsers' => $canManageUsers ? User::count() : null,
                'totalSubscribers' => $canManageSubscribers ? Subscriber::count() : null,
                'pendingStartups' => $pendingStartups,
                'heroRequests' => $heroRequests,
                'pendingReports' => $pendingReports,
                'unreadMessages' => $unreadMessages,
                'failedWebsiteChecks' => $failedWebsiteChecks,
                'failedTasks' => $failedTasks,
                'pendingAds' => $pendingAds,
                'canManageStartups' => $canManageStartups,
                'canManageUsers' => $canManageUsers,
                'canManageSubscribers' => $canManageSubscribers,
                'canManagePayments' => $canManagePayments,
                'canManageMessages' => $canManageMessages,
                'canManageReports' => $canManageReports,
                'canManageWebsites' => $canManageWebsites,
                'canManageTasks' => $canManageTasks,
                'canManageAds' => $canManageAds,
                'days' => $days,
                'revenueByDay' => $revenueByDay,
                'usersByDay' => $usersByDay,
            ])->render(),
        ]);
    }
}
