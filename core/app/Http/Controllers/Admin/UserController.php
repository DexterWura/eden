<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function startups(User $user)
    {
        $startups = $user->startups()->orderBy('name')->get();
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
