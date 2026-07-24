<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\StartupClaimVerification;
use App\Services\StartupClaimService;
use App\Services\StartupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimController extends EdenController
{
    public function __construct(
        private StartupService $startupService,
        private StartupClaimService $claimService
    ) {}

    private function getStartup(string $slug): Startup
    {
        $startup = Startup::where('slug', $slug)->firstOrFail();
        if ($startup->status !== Startup::STATUS_ACTIVE) {
            abort(404);
        }
        return $startup;
    }

    public function show(Request $request, string $slug)
    {
        if (! auth()->check()) {
            return redirect()->guest(route('login'))
                ->with('info', 'Please log in or sign up to claim this startup.');
        }

        $startup = $this->getStartup($slug);

        $step = $request->query('step', 'confirm');
        $pending = StartupClaimVerification::where('startup_id', $startup->id)
            ->where('user_id', auth()->id())
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if ($step === 'verify' && $pending) {
            $instructions = $this->claimService->getDnsInstructions($pending->verification_code);
            $domain = $this->claimService->getDomainFromWebsite($startup->website);
            $baseUrl = $this->claimService->getBaseUrl($startup->website);
            $fileUrl = $pending->verification_file_name && $baseUrl
                ? rtrim($baseUrl, '/') . '/' . $pending->verification_file_name
                : null;

            return $this->page('claim-verify', 'Claim ' . $startup->name, null, [
                'startup' => $startup,
                'pending' => $pending,
                'dnsRecord' => $instructions,
                'domain' => $domain,
                'fileUrl' => $fileUrl,
            ]);
        }

        if ($step === 'method') {
            return $this->page('claim-method', 'Claim ' . $startup->name, null, [
                'startup' => $startup,
            ]);
        }

        return $this->page('claim-intro', 'Claim ' . $startup->name, null, [
            'startup' => $startup,
        ]);
    }

    public function confirm(Request $request, string $slug): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Please log in to claim this startup.');
        }

        $startup = $this->getStartup($slug);

        $request->validate(['confirm' => 'required|in:yes']);

        return redirect()->route('startup.claim', ['slug' => $slug, 'step' => 'method']);
    }

    public function startVerification(Request $request, string $slug): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Please log in to claim this startup.');
        }

        $startup = $this->getStartup($slug);

        $request->validate([
            'method' => 'required|in:dns,file',
        ]);

        $method = $request->input('method');
        if ($method === StartupClaimVerification::METHOD_FILE && ! $startup->website) {
            throw ValidationException::withMessages([
                'method' => ['File verification requires the app to have a website URL.'],
            ]);
        }
        if ($method === StartupClaimVerification::METHOD_DNS && ! $this->claimService->getDomainFromWebsite($startup->website)) {
            throw ValidationException::withMessages([
                'method' => ['DNS verification requires the app to have a website URL.'],
            ]);
        }

        $this->claimService->createVerification($startup, (int) auth()->id(), $method);

        return redirect()->route('startup.claim', ['slug' => $slug, 'step' => 'verify']);
    }

    public function verify(Request $request, string $slug): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Please log in to claim this startup.');
        }

        $startup = $this->getStartup($slug);

        $pending = StartupClaimVerification::where('startup_id', $startup->id)
            ->where('user_id', auth()->id())
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $pending) {
            return redirect()->route('startup.claim', ['slug' => $slug])
                ->with('error', 'No pending verification. Please start again.');
        }

        $verified = false;
        if ($pending->method === StartupClaimVerification::METHOD_DNS) {
            $verified = $this->claimService->verifyDns($startup, $pending->verification_code);
        } elseif ($pending->method === StartupClaimVerification::METHOD_FILE && $pending->verification_file_name) {
            $verified = $this->claimService->verifyFile(
                $startup,
                $pending->verification_file_name,
                $pending->verification_code
            );
        }

        if (! $verified) {
            return redirect()->route('startup.claim', ['slug' => $slug, 'step' => 'verify'])
                ->with('error', 'Verification failed. Ensure the DNS record or file is in place and try again.');
        }

        $pending->update(['verified_at' => now()]);
        $startup->update(['user_id' => auth()->id()]);

        return redirect()->route('founder.dashboard')
            ->with('notify', [['success', 'You have successfully claimed ' . $startup->name . '. You can now manage it from your dashboard.']]);
    }
}
