<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function affiliate()
    {
        $pageTitle = 'Affiliate Report';
        $totalReferrals = User::whereNotNull('ref_by')->where('ref_by', '>', 0)->count();
        $totalPayout = Transaction::where('remark', 'affiliate_signup')->where('trx_type', '+')->sum('amount');
        $topReferrers = User::has('referredUsers')
            ->withCount('referredUsers')
            ->orderByDesc('referred_users_count')
            ->take(50)
            ->get()
            ->map(function ($user) {
                $user->affiliate_earned = Transaction::where('user_id', $user->id)->where('remark', 'affiliate_signup')->where('trx_type', '+')->sum('amount');
                return $user;
            });
        return view('admin.reports.affiliate', compact('pageTitle', 'totalReferrals', 'totalPayout', 'topReferrers'));
    }

    public function transaction(Request $request, $user_id = null)
    {
        $pageTitle = $user_id ? 'User Transactions' : 'Transaction Report';
        $remarks = Transaction::select('remark')->whereNotNull('remark')->distinct()->orderBy('remark')->get();
        $query = Transaction::with('user');
        if ($user_id) {
            $query->where('user_id', $user_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('trx', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($uq) use ($request) {
                        $uq->where('username', 'like', '%' . $request->search . '%');
                    });
            });
        }
        if ($request->trx_type) {
            $query->where('trx_type', $request->trx_type);
        }
        if ($request->remark) {
            $query->where('remark', $request->remark);
        }
        if ($request->date) {
            $dates = explode('-', $request->date);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', trim($dates[0]))->whereDate('created_at', '<=', trim($dates[1]));
            }
        }
        $transactions = $query->orderBy('id', 'desc')->paginate(getPaginate())->appends($request->all());
        $emptyMessage = 'No transactions found';
        return view('admin.reports.transactions', compact('pageTitle', 'transactions', 'remarks', 'emptyMessage'));
    }

    public function loginHistory(Request $request)
    {
        $pageTitle = 'Login History';
        $query = UserLogin::with('user')->orderBy('id', 'desc');
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->date) {
            $dates = explode('-', $request->date);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', trim($dates[0]))->whereDate('created_at', '<=', trim($dates[1]));
            }
        }
        $loginLogs = $query->paginate(getPaginate())->appends($request->all());
        $emptyMessage = 'No login logs found';
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs', 'emptyMessage'));
    }

    public function loginIpHistory($ip)
    {
        $pageTitle = 'Login IP History: ' . $ip;
        $loginLogs = UserLogin::with('user')->where('user_ip', $ip)->orderBy('id', 'desc')->paginate(getPaginate());
        $emptyMessage = 'No login logs for this IP';
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs', 'emptyMessage', 'ip'));
    }

    public function notificationHistory(Request $request)
    {
        $pageTitle = 'Notification History';
        $query = NotificationLog::with('user')->orderBy('id', 'desc');
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->date) {
            $dates = explode('-', $request->date);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', trim($dates[0]))->whereDate('created_at', '<=', trim($dates[1]));
            }
        }
        $logs = $query->paginate(getPaginate())->appends($request->all());
        $emptyMessage = 'No notifications found';
        $user = null;
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'emptyMessage', 'user'));
    }

    public function emailDetails($id)
    {
        $email = NotificationLog::findOrFail($id);
        $pageTitle = 'Email Details';
        return view('admin.reports.email_details', compact('pageTitle', 'email'));
    }

    public function index(Request $request)
    {
        $pageTitle = 'User Reports';
        $query = Report::with(['user', 'reportable'])->latest();
        if ($request->status !== null && $request->status !== '') {
            $query->where('status', (int) $request->status);
        }
        if ($request->type) {
            $type = $request->type === 'listing' ? \App\Models\Listing::class : \App\Models\User::class;
            $query->where('reportable_type', $type);
        }
        $reports = $query->paginate(getPaginate())->appends($request->all());
        return view('admin.report.index', compact('pageTitle', 'reports'));
    }

    public function show($id)
    {
        $pageTitle = 'Report Details';
        $report = Report::with(['user', 'reportable'])->findOrFail($id);
        return view('admin.report.show', compact('pageTitle', 'report'));
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $request->validate([
            'status' => 'required|in:0,1',
            'admin_notes' => 'nullable|string|max:2000',
        ]);
        $report->status = (int) $request->status;
        $report->admin_notes = $request->admin_notes;
        if ((int) $request->status === Report::STATUS_REVIEWED) {
            $report->reviewed_at = now();
            $report->reviewed_by = auth()->guard('admin')->id();
        }
        $report->save();
        $notify[] = ['success', 'Report updated'];
        return back()->withNotify($notify);
    }
}
