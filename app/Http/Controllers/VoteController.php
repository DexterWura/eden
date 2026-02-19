<?php

namespace App\Http\Controllers;

use App\Models\Startup;
use App\Services\StartupGrowthService;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Startup $startup)
    {
        $exists = $startup->votes()->where('user_id', auth()->id())->exists();
        if (! $exists) {
            $startup->votes()->create(['user_id' => auth()->id()]);
            app(StartupGrowthService::class)->recalculateStatus($startup);
        }
        if (request()->expectsJson()) {
            return response()->json(['votes' => $startup->votes()->count()]);
        }
        return back();
    }
}
