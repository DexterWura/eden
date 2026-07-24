<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function leave(Request $request): RedirectResponse
    {
        if (! $request->session()->has('eden_impersonator_admin_id')) {
            return redirect('/');
        }

        Auth::guard('web')->logout();
        $request->session()->forget('eden_impersonator_admin_id');

        if (! Auth::guard('admin')->check()) {
            return redirect('/')->with('info', 'Impersonation ended.');
        }

        return redirect()->route('admin.users.index')
            ->with('notify', [['success', 'Returned to admin. User session ended.']]);
    }
}
