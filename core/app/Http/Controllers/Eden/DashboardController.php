<?php

namespace App\Http\Controllers\Eden;

use App\Models\ContactSubmission;
use App\Models\Startup;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
        $unreadNotifications = $user->unreadNotifications;

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
                'unreadNotifications' => $unreadNotifications,
            ])->render(),
        ]);
    }

    public function requestHeroFeature(Startup $startup): RedirectResponse
    {
        $user = auth()->user();

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
        $totalStartups = Startup::count();
        $activeStartups = Startup::active()->count();
        $launchingToday = Startup::active()->launchingToday()->count();
        $recentStartups = Startup::query()->orderByDesc('created_at')->limit(5)->get();
        $recentContactMessages = ContactSubmission::query()->orderByDesc('created_at')->limit(10)->get();
        $heroRequests = Startup::where('hero_request_status', 'pending')->orderBy('updated_at')->get();
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
                'heroRequests' => $heroRequests,
            ])->render(),
        ]);
    }
}
