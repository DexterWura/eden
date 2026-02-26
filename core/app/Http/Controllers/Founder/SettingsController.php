<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $content = view('eden.founder.settings', ['user' => $user])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Settings',
            'sidebar' => 'founder',
            'activeNav' => 'settings',
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }
        $validator = Validator::make($request->all(), $rules, []);
        if ($validator->fails()) {
            return redirect()->route('founder.settings')->withErrors($validator)->withInput();
        }
        $data = $validator->validated();

        // Apply only the allowed fields explicitly to avoid mass-assignment issues
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        return redirect()->route('founder.settings')->with('notify', [['success', 'Profile updated.']]);
    }
}
