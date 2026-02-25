<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    private function dashboardVars(string $title, string $activeNav, string $content): array
    {
        return [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => 'Eden Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
        ];
    }
}
