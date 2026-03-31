<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StartupReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StartupReportController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->get('status', StartupReport::STATUS_PENDING);

        $query = StartupReport::query()
            ->with(['startup:id,name,slug'])
            ->orderByDesc('created_at');

        if ($status !== '') {
            $query->where('status', $status);
        }

        $reports = $query->paginate(25)->withQueryString();

        $content = view('eden.startup-reports.index', [
            'reports' => $reports,
            'status' => $status,
            'reasonLabels' => StartupReport::reasonLabels(),
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Listing reports', 'startup-reports', $content));
    }

    public function resolve(Request $request, StartupReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $report->status = StartupReport::STATUS_REVIEWED;
        $report->admin_notes = $validated['admin_notes'] ?? $report->admin_notes;
        $report->reviewed_at = now();
        $report->save();

        return redirect()
            ->route('admin.startup-reports.index')
            ->with('notify', [['success', 'Report marked as reviewed.']]);
    }

    public function dismiss(Request $request, StartupReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $report->status = StartupReport::STATUS_DISMISSED;
        $report->admin_notes = $validated['admin_notes'] ?? $report->admin_notes;
        $report->reviewed_at = now();
        $report->save();

        return redirect()
            ->route('admin.startup-reports.index')
            ->with('notify', [['success', 'Report dismissed.']]);
    }

    private function dashboardVars(string $title, string $activeNav, string $content): array
    {
        return [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ];
    }
}
