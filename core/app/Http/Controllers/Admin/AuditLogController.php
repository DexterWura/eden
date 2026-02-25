<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct()
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            abort(403, 'Only super administrators can view the audit log.');
        }
    }

    /**
     * List audit log entries with filters.
     */
    public function index(Request $request)
    {
        $pageTitle = 'Audit Log';
        $query = AdminAuditLog::with('admin')->orderBy('id', 'desc');

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        if ($request->filled('date')) {
            $dates = explode('-', $request->date);
            if (count($dates) === 2) {
                try {
                    $start = Carbon::parse(trim($dates[0]))->startOfDay();
                    $end = Carbon::parse(trim($dates[1]))->endOfDay();
                    $query->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {
                    // ignore invalid date
                }
            }
        }

        $logs = $query->paginate(getPaginate());
        $admins = Admin::orderBy('name')->get(['id', 'name', 'username']);

        return view('admin.audit.index', compact('pageTitle', 'logs', 'admins'));
    }
}
