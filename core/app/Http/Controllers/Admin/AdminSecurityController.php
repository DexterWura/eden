<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurityController extends Controller
{
    public function profile(): Response
    {
        return $this->page('Admin profile', 'profile', ['admin' => auth('admin')->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = auth('admin')->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
        ]);
        $before = $admin->only(['name', 'email']);
        $admin->update($validated);
        admin_audit_log('profile.updated', 'Admin profile updated.', $admin, $before, $validated);

        return back()->with('notify', [['success', 'Profile updated.']]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);
        $admin = auth('admin')->user();
        $admin->update(['password' => Hash::make($validated['password'])]);
        admin_audit_log('profile.password_changed', 'Admin password changed.', $admin);
        auth('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('notify', [['success', 'Password changed. Sign in again to continue.']]);
    }

    public function beginTwoFactor(Request $request, TotpService $totp): Response
    {
        $request->validate(['current_password' => ['required', 'current_password:admin']]);
        $secret = $totp->generateSecret();
        $request->session()->put('admin_2fa_pending_secret', $secret);
        $admin = auth('admin')->user();

        return $this->page('Enable two-factor authentication', 'two-factor', [
            'secret' => $secret,
            'provisioningUri' => $totp->provisioningUri(
                $secret,
                $admin->email,
                function_exists('gs') ? (string) gs('site_name') : 'Eden'
            ),
        ]);
    }

    public function confirmTwoFactor(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $secret = (string) $request->session()->get('admin_2fa_pending_secret');
        if ($secret === '' || ! $totp->verify($secret, $validated['code'])) {
            return back()->withErrors(['code' => 'The authenticator code is invalid or expired.']);
        }

        $codes = $totp->generateRecoveryCodes();
        $admin = auth('admin')->user();
        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => array_map(fn (string $code): string => Hash::make($code), $codes),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $request->session()->forget('admin_2fa_pending_secret');
        $request->session()->put('admin_2fa_verified_id', $admin->id);
        admin_audit_log('security.2fa_enabled', 'Two-factor authentication enabled.', $admin);

        return redirect()->route('admin.security.profile')
            ->with('recovery_codes', $codes)
            ->with('notify', [['success', 'Two-factor authentication enabled. Store the recovery codes now.']]);
    }

    public function disableTwoFactor(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'code' => ['required', 'string'],
        ]);
        $admin = auth('admin')->user();
        if (! $this->validCodeOrRecovery($admin, $validated['code'], $totp)) {
            return back()->withErrors(['code' => 'The authentication code is invalid.']);
        }

        $admin->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $request->session()->forget(['admin_2fa_verified_id', 'admin_reconfirmed_at']);
        admin_audit_log('security.2fa_disabled', 'Two-factor authentication disabled.', $admin);

        return back()->with('notify', [['success', 'Two-factor authentication disabled.']]);
    }

    public function challenge(): Response
    {
        return $this->page('Two-factor challenge', 'challenge');
    }

    public function verifyChallenge(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:32']]);
        $admin = auth('admin')->user();
        if (! $this->validCodeOrRecovery($admin, $validated['code'], $totp)) {
            return back()->withErrors(['code' => 'The authentication code is invalid.']);
        }

        $request->session()->put('admin_2fa_verified_id', $admin->id);
        return redirect()->intended(route('admin.dashboard'));
    }

    public function reconfirm(Request $request): Response
    {
        return $this->page('Confirm sensitive action', 'reconfirm');
    }

    public function verifyReconfirmation(Request $request, TotpService $totp): RedirectResponse
    {
        $rules = ['current_password' => ['required', 'current_password:admin']];
        if (auth('admin')->user()->hasTwoFactorEnabled()) {
            $rules['code'] = ['required', 'string', 'max:32'];
        }
        $validated = $request->validate($rules);
        $admin = auth('admin')->user();
        if ($admin->hasTwoFactorEnabled() && ! $this->validCodeOrRecovery($admin, $validated['code'], $totp)) {
            return back()->withErrors(['code' => 'The authentication code is invalid.']);
        }

        $request->session()->put('admin_reconfirmed_at', time());
        $returnTo = $request->session()->pull('admin_reconfirm_return_to', route('admin.dashboard'));
        if (! is_string($returnTo)
            || ! str_starts_with($returnTo, '/')
            || str_starts_with($returnTo, '//')
            || str_contains($returnTo, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $returnTo)
        ) {
            $returnTo = route('admin.dashboard', absolute: false);
        }
        return redirect()->to($returnTo)->with('notify', [['success', 'Identity confirmed. Repeat the sensitive action to continue.']]);
    }

    private function validCodeOrRecovery($admin, string $code, TotpService $totp): bool
    {
        if ($admin->two_factor_secret && $totp->verify($admin->two_factor_secret, $code)) {
            return true;
        }

        $normalized = strtoupper(trim($code));
        $codes = $admin->two_factor_recovery_codes ?? [];
        foreach ($codes as $index => $hash) {
            if (Hash::check($normalized, $hash)) {
                unset($codes[$index]);
                $admin->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
                return true;
            }
        }

        return false;
    }

    private function page(string $title, string $view, array $data = []): Response
    {
        $content = view("eden.admin-operations.security.{$view}", $data)->render();
        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => 'admin-profile',
            'dashboardLogo' => (function_exists('gs') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => auth('admin')->user()->name,
            'avatarLetter' => strtoupper(substr(auth('admin')->user()->name, 0, 1)),
            'content' => $content,
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }
}
