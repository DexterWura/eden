<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Startup;
use App\Services\ClaimVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Startup $startup)
    {
        if (! $startup->approved_at) {
            return redirect()->route('startups.index')->with('info', 'This listing is not approved yet. Only approved listings can be claimed.');
        }
        if ($startup->user_id && $startup->user_id === auth()->id()) {
            return redirect()->route('startups.show', $startup->slug)->with('info', 'You already own this startup.');
        }
        $claim = Claim::where('startup_id', $startup->id)->where('user_id', auth()->id())->whereNull('verified_at')->first();
        if (! $claim) {
            $claim = Claim::create([
                'startup_id' => $startup->id,
                'user_id' => auth()->id(),
                'verification_token' => Str::random(32),
            ]);
        }
        return view('claim.verify', compact('startup', 'claim'));
    }

    public function verify(Claim $claim)
    {
        if ($claim->user_id !== auth()->id() || $claim->verified_at) {
            return redirect()->route('startups.show', $claim->startup->slug);
        }
        $verified = app(ClaimVerificationService::class)->verify($claim);
        if ($verified) {
            return redirect()->route('startups.show', $claim->startup->slug)->with('success', 'Startup claimed successfully.');
        }
        return back()->with('error', 'Verification failed. Ensure the meta tag is on your site and try again.');
    }
}
