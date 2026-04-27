<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\StartupFundingRound;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FundraisingController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isPro()) {
            return redirect()->route('pricing')
                ->with('info', 'Pro membership required to manage fundraising rounds.');
        }

        $startups = Startup::visibleToUser($user)
            ->with('activeFundingRound')
            ->orderBy('name')
            ->get();

        $content = view('eden.founder.fundraising', [
            'startups' => $startups,
            'fundingRoundTypes' => StartupFundingRound::ROUND_TYPES,
        ])->render();

        return $this->layoutResponse('Fund raising', 'fundraising', $content);
    }

    public function update(Request $request, Startup $startup): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isPro()) {
            return redirect()->route('pricing')
                ->with('info', 'Pro membership required to manage fundraising rounds.');
        }

        $this->authorizeStartup($startup);

        $validated = $request->validate([
            'seeking_investors' => ['nullable', 'in:0,1'],
            'funding_round_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(StartupFundingRound::ROUND_TYPES))],
            'funding_amount_seeking' => ['nullable', 'numeric', 'min:0'],
            'funding_currency' => ['nullable', 'string', 'max:3'],
            'funding_contact_email' => ['nullable', 'email', 'max:255'],
            'funding_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->applyFundingRound($startup, $validated);

        return redirect()->route('founder.fundraising.index')
            ->with('notify', [['success', 'Fundraising settings updated for ' . $startup->name . '.']]);
    }

    private function applyFundingRound(Startup $startup, array $validated): void
    {
        $seeking = ($validated['seeking_investors'] ?? '0') === '1';
        $openRound = $startup->activeFundingRound;

        if (! $seeking) {
            if ($openRound) {
                $openRound->update(['status' => StartupFundingRound::STATUS_CLOSED]);
            }
            return;
        }

        $roundType = $validated['funding_round_type'] ?? 'seed';
        if (! array_key_exists($roundType, StartupFundingRound::ROUND_TYPES)) {
            $roundType = 'seed';
        }

        $payload = [
            'round_type' => $roundType,
            'amount_seeking' => $validated['funding_amount_seeking'] ?? null,
            'currency' => strtoupper(substr((string) ($validated['funding_currency'] ?? 'USD'), 0, 3)) ?: 'USD',
            'description' => $validated['funding_description'] ?: null,
            'contact_email' => $validated['funding_contact_email'] ?: null,
            'status' => StartupFundingRound::STATUS_OPEN,
        ];

        if ($openRound) {
            $openRound->update($payload);
            return;
        }

        StartupFundingRound::create(array_merge(['startup_id' => $startup->id], $payload));
    }

    private function authorizeStartup(Startup $startup): void
    {
        if (! $startup->userCanManage(auth()->user())) {
            abort(403, 'You do not have permission to manage this startup.');
        }
    }

    private function layoutResponse(string $title, string $activeNav, string $content): Response
    {
        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'founder',
            'activeNav' => $activeNav,
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search...',
            'avatarTitle' => auth()->user()->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)),
            'notifyPartial' => view('partials.notify')->render(),
            'content' => $content,
        ]);
    }
}
