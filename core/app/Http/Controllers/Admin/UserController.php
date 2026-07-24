<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $content = view('eden.users.index', [
            'users' => $users,
            'search' => $search,
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

    public function giftPro(User $user): RedirectResponse
    {
        $user->is_pro = !$user->is_pro;
        $user->pro_since = $user->is_pro ? now() : null;
        $user->save();

        $message = $user->is_pro ? 'Pro membership granted to ' . $user->name . '.' : 'Pro membership revoked from ' . $user->name . '.';
        return redirect()->route('admin.users.index')->with('notify', [['success', $message]]);
    }

    public function loginAs(Request $request, User $user): RedirectResponse
    {
        if ((int) ($user->status ?? Status::USER_ACTIVE) !== Status::USER_ACTIVE) {
            return redirect()->route('admin.users.index')
                ->with('notify', [['error', 'Cannot sign in as a disabled user.']]);
        }

        $admin = $request->user('admin');
        if ($admin === null) {
            return redirect()->route('admin.users.index')
                ->with('notify', [['error', 'Admin session required to sign in as a user.']]);
        }

        Auth::guard('web')->login($user);
        $request->session()->put('eden_impersonator_admin_id', $admin->id);

        admin_audit_log(
            'user.login_as',
            'Signed in as user: ' . $user->email,
            $user,
            [],
            ['user_id' => $user->id, 'admin_id' => $admin->id]
        );

        return redirect()->route('founder.dashboard')
            ->with('notify', [['success', 'Viewing as ' . $user->name . '. Use “Return to admin” when finished.']]);
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
