<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\StartupFundingRound;
use App\Services\StartupFundingRoundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FundraisingController extends Controller
{
    public function __construct(
        private StartupFundingRoundService $fundingRoundService
    ) {
        parent::__construct();
    }

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

        $this->authorize('manage', $startup);

        $validated = $request->validate([
            'seeking_investors' => ['nullable', 'in:0,1'],
            'funding_round_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(StartupFundingRound::ROUND_TYPES))],
            'funding_amount_seeking' => ['nullable', 'numeric', 'min:0'],
            'funding_currency' => ['nullable', 'string', 'max:3'],
            'funding_contact_email' => ['nullable', 'email', 'max:255'],
            'funding_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->fundingRoundService->sync($startup, $validated);

        return redirect()->route('founder.fundraising.index')
            ->with('notify', [['success', 'Fundraising settings updated for ' . $startup->name . '.']]);
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
