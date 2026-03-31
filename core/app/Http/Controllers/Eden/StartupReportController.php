<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\StartupReport;
use App\Services\StartupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StartupReportController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function store(Request $request, string $slug): RedirectResponse
    {
        if ($request->filled('website')) {
            return redirect()->route('startup.show', $slug)->with('success', 'Thanks — we will review this listing.');
        }

        $startup = $this->startupService->getBySlug($slug);

        $validated = $request->validate([
            'reporter_email' => 'required|email|max:255',
            'reason' => 'required|string|in:' . implode(',', [
                StartupReport::REASON_SPAM,
                StartupReport::REASON_MISLEADING,
                StartupReport::REASON_WRONG_CATEGORY,
                StartupReport::REASON_IMPERSONATION,
                StartupReport::REASON_OTHER,
            ]),
            'details' => 'nullable|string|max:2000',
        ]);

        if ($validated['reason'] === StartupReport::REASON_OTHER && empty(trim((string) ($validated['details'] ?? '')))) {
            return redirect()
                ->route('startup.show', $slug)
                ->withInput()
                ->with('error', 'Please add a short note when you choose “Other”.');
        }

        StartupReport::create([
            'startup_id' => $startup->id,
            'user_id' => auth()->id(),
            'reporter_email' => $validated['reporter_email'],
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => StartupReport::STATUS_PENDING,
        ]);

        return redirect()
            ->route('startup.show', $slug)
            ->with('success', 'Thanks for the report. Our team will review it.');
    }
}
