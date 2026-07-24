<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Startup;
use App\Rules\SensiblePersonName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $rules = [
            'name' => ['required', 'string', 'max:80', new SensiblePersonName()],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'notification_preferences' => ['nullable', 'array'],
        ];
        foreach (array_keys(config('notification_preferences.types', [])) as $preference) {
            $rules["notification_preferences.{$preference}"] = ['required', 'boolean'];
        }
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
        $configuredPreferences = collect(config('notification_preferences.types', []))
            ->keys()
            ->mapWithKeys(fn (string $preference): array => [
                $preference => (bool) data_get($data, "notification_preferences.{$preference}", false),
            ])
            ->all();
        $legacyOptOuts = collect($user->notification_preferences ?? [])
            ->reject(fn ($enabled, string $preference): bool => array_key_exists($preference, $configuredPreferences) || (bool) $enabled)
            ->all();
        $user->notification_preferences = array_merge($legacyOptOuts, $configuredPreferences);

        if (!empty($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        Startup::syncUserToFounderRecords($user);
        return redirect()->route('founder.settings')->with('notify', [['success', 'Profile updated.']]);
    }

    public function destroyData(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_delete' => ['required', 'accepted'],
            'confirm_phrase' => ['required', 'in:DELETE'],
        ], [
            'confirm_phrase.in' => 'Type DELETE to confirm.',
        ]);

        $user = auth()->user();

        DB::transaction(function () use ($user) {
            BlogPost::where('author_id', $user->id)->delete();
            $user->startups()->delete();
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('notify', [['success', 'Your account and founder data were deleted.']]);
    }
}
