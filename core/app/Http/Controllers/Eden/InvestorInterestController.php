<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use App\Models\InvestorLead;
use App\Models\Startup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvestorInterestController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'organization' => ['nullable', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'max:0'],
        ]);

        $startup = Startup::query()->active()->where('slug', $slug)->firstOrFail();
        $round = $startup->activeFundingRound()->firstOrFail();
        $email = mb_strtolower(trim($validated['email']));
        $round->investorLeads()->firstOrCreate(
            ['email' => $email],
            [
                'name' => trim($validated['name']),
                'organization' => trim((string) ($validated['organization'] ?? '')) ?: null,
                'message' => trim((string) ($validated['message'] ?? '')) ?: null,
                'status' => InvestorLead::STATUS_NEW,
                'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), config('app.key')) : null,
            ]
        );

        return back()->with('success', 'Your interest was sent privately to the founder.');
    }
}
