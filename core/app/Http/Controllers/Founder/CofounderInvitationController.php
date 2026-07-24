<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Services\CofounderInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CofounderInvitationController extends Controller
{
    public function __construct(
        private CofounderInvitationService $invitationService
    ) {
        parent::__construct();
    }

    public function store(Request $request, Startup $startup): RedirectResponse
    {
        $this->authorize('manage', $startup);
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255', 'not_in:' . auth()->user()->email],
        ]);

        $this->invitationService->invite($startup, auth()->user(), $validated['email']);

        return back()->with('notify', [['success', 'Co-founder invitation sent.']]);
    }

    public function show(string $token): Response
    {
        $invitation = $this->invitationService->findUsable($token);

        return response()->view('eden.cofounder-invitation', compact('invitation', 'token'));
    }

    public function accept(string $token): RedirectResponse
    {
        $startup = $this->invitationService->accept($token, auth()->user());

        return redirect()->route('founder.startups.edit', $startup)
            ->with('notify', [['success', 'You now have co-founder access to ' . $startup->name . '.']]);
    }
}
