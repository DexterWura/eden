<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderByDesc('created_at');
        $search = $request->get('q', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        $users = $query->paginate(20)->withQueryString();
        $linkedinConfigured = $this->isLinkedInConfigured();
        if ($linkedinConfigured) {
            $users->load('startups');
            foreach ($users as $user) {
                $user->has_linkedin_link = $this->userHasLinkedInLink($user);
            }
        } else {
            foreach ($users as $user) {
                $user->has_linkedin_link = false;
            }
        }

        $content = view('eden.users.index', [
            'users' => $users,
            'search' => $search,
            'linkedinConfigured' => $linkedinConfigured,
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Users', 'users', $content));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $isActive = (int) ($user->status ?? Status::USER_ACTIVE) === Status::USER_ACTIVE;
        $user->status = $isActive ? Status::USER_BAN : Status::USER_ACTIVE;
        if ($user->status === Status::USER_ACTIVE && isset($user->ban_reason)) {
            $user->ban_reason = null;
        }
        $user->save();

        $message = $user->status === Status::USER_ACTIVE ? 'User enabled.' : 'User disabled.';
        return redirect()->route('admin.users.index')
            ->with('notify', [['success', $message]]);
    }

    public function toggleFeaturedOnHero(Request $request, User $user): RedirectResponse
    {
        $current = (bool) ($user->featured_on_hero ?? false);
        $user->featured_on_hero = ! $current;
        $user->save();
        $message = $user->featured_on_hero ? ($user->name . ' featured on hero.') : ($user->name . ' removed from hero.');
        $back = $request->input('_redirect', 'startups');
        $route = $back === 'users' ? 'admin.users.index' : 'admin.startups.index';
        return redirect()->route($route)
            ->with('notify', [['success', $message]]);
    }

    public function startups(User $user)
    {
        $startups = Startup::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('founder_email', $user->email);
                if (! empty(trim((string) ($user->email ?? '')))) {
                    $q->orWhereRaw(
                        "founders IS NOT NULL AND JSON_SEARCH(founders, 'one', ?, NULL, '$[*].email') IS NOT NULL",
                        [$user->email]
                    );
                }
            })
            ->orderBy('name')
            ->get();

        $content = view('eden.users.startups', [
            'user' => $user,
            'startups' => $startups,
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars(
            'Startups: ' . $user->name,
            'users',
            $content
        ));
    }

    private function isLinkedInConfigured(): bool
    {
        if (! Schema::hasTable('general_settings')) {
            return false;
        }
        if (! Schema::hasColumn('general_settings', 'linkedin_client_id') || ! Schema::hasColumn('general_settings', 'linkedin_client_secret')) {
            return false;
        }
        $row = DB::table('general_settings')->first();
        if (! $row) {
            return false;
        }
        $id = trim((string) ($row->linkedin_client_id ?? ''));
        $secret = trim((string) ($row->linkedin_client_secret ?? ''));
        return $id !== '' && $secret !== '';
    }

    private function userHasLinkedInLink(User $user): bool
    {
        if (! empty(trim((string) ($user->linkedin_url ?? '')))) {
            return true;
        }
        foreach ($user->startups ?? [] as $startup) {
            if (! empty(trim((string) ($startup->founder_linkedin_url ?? '')))) {
                return true;
            }
            $founders = $startup->founders ?? [];
            foreach ($founders as $f) {
                $url = is_array($f) ? ($f['linkedin_url'] ?? null) : ($f->linkedin_url ?? null);
                if (! empty(trim((string) ($url ?? '')))) {
                    return true;
                }
            }
        }
        return false;
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
            'notifyPartial' => view('partials.notify')->render(),
        ];
    }
}
