<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Constants\Status;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token)
    {
        $pageTitle = "Account Recovery";
        $resetToken = AdminPasswordReset::whereKey($token)
            ->where('status', Status::ENABLE)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (! $resetToken || ! hash_equals((string) session('admin_password_reset_id'), (string) $resetToken->id)) {
            $notify[] = ['error', 'Verification code mismatch'];
            return to_route('admin.password.reset')->withNotify($notify);
        }
        $email = $resetToken->email;
        return view('admin.auth.passwords.reset', compact('pageTitle', 'token'));
    }


    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|integer',
            'password' => 'required|string|min:12|confirmed',
        ]);

        $reset = AdminPasswordReset::whereKey($request->token)
            ->where('status', Status::ENABLE)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();
        if (! $reset || ! hash_equals((string) session('admin_password_reset_id'), (string) $reset->id)) {
            $notify[] = ['error', 'Invalid code'];
            return to_route('admin.password.reset')->withNotify($notify);
        }

        $admin = Admin::where('email', $reset->email)->where('status', Admin::STATUS_ENABLED)->first();
        if (! $admin) {
            $reset->update(['status' => Status::DISABLE]);
            session()->forget(['admin_password_reset_id', 'pass_res_mail']);
            return to_route('admin.login')->withNotify([['success', 'Password reset complete. You can now sign in.']]);
        }

        $admin->password = Hash::make($request->password);
        $admin->save();
        $reset->status = Status::DISABLE;
        $reset->save();
        AdminPasswordReset::where('email', $admin->email)->where('id', '!=', $reset->id)->update(['status' => Status::DISABLE]);
        session()->invalidate();
        session()->regenerateToken();

        $ipInfo = getIpInfo();
        $browser = osBrowser();
        notify($admin, 'PASS_RESET_DONE', [
            'operating_system' => $browser['os_platform'],
            'browser' => $browser['browser'],
            'ip' => $ipInfo['ip'],
            'time' => $ipInfo['time']
        ],['email'],false);

        $notify[] = ['success', 'Password changed'];
        return to_route('admin.login')->withNotify($notify);
    }
}
