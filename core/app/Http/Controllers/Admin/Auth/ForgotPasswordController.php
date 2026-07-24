<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Constants\Status;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{

    public function showLinkRequestForm()
    {
        $pageTitle = 'Account Recovery';
        return view('admin.auth.passwords.email', compact('pageTitle'));
    }

    public function sendResetCodeEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        if(!verifyCaptcha()){
            $notify[] = ['error','Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $email = mb_strtolower(trim($request->email));
        $admin = Admin::whereRaw('LOWER(email) = ?', [$email])->where('status', Admin::STATUS_ENABLED)->first();
        session()->put('pass_res_mail', $email);
        session()->forget('admin_password_reset_id');

        if ($admin) {
            AdminPasswordReset::where('email', $admin->email)->update(['status' => Status::DISABLE]);
            $code = verificationCode(6);
            $adminPasswordReset = new AdminPasswordReset();
            $adminPasswordReset->email = $admin->email;
            $adminPasswordReset->token = Hash::make($code);
            $adminPasswordReset->status = Status::ENABLE;
            $adminPasswordReset->created_at = Carbon::now();
            $adminPasswordReset->save();

            $adminIpInfo = getIpInfo();
            $adminBrowser = osBrowser();
            notify($admin, 'PASS_RESET_CODE', [
                'code' => $code,
                'operating_system' => $adminBrowser['os_platform'],
                'browser' => $adminBrowser['browser'],
                'ip' => $adminIpInfo['ip'],
                'time' => $adminIpInfo['time']
            ], ['email'], false);
        }

        return to_route('admin.password.code.verify')
            ->withNotify([['success', 'If an enabled admin account matches that email, a reset code has been sent.']]);
    }

    public function codeVerify(){
        $pageTitle = 'Verify Code';
        $email = session()->get('pass_res_mail');
        if (!$email) {
            $notify[] = ['error','Oops! session expired'];
            return to_route('admin.password.reset')->withNotify($notify);
        }
        return view('admin.auth.passwords.code_verify', compact('pageTitle','email'));
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $adminPasswordReset = AdminPasswordReset::where('email', session()->get('pass_res_mail'))
            ->where('status', Status::ENABLE)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->orderByDesc('id')
            ->first();

        if (! $adminPasswordReset || ! Hash::check($request->code, $adminPasswordReset->token)) {
            $notify[] = ['error', 'Verification code does not match'];
            return to_route('admin.password.code.verify')->withNotify($notify);
        }

        session()->put('admin_password_reset_id', $adminPasswordReset->id);
        $notify[] = ['success', 'You can change your password'];
        return to_route('admin.password.reset.form', $adminPasswordReset->id)->withNotify($notify);
    }
}
