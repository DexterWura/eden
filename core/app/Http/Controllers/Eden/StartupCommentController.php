<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\StartupComment;
use App\Rules\SensibleCommentBody;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StartupCommentController extends EdenController
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $startup = Startup::where('slug', $slug)->first();
        if (! $startup || ! $startup->isActive()) {
            return redirect()->back()->with('error', 'Startup not found or not available.');
        }

        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'body' => ['required', 'string', 'min:1', 'max:2000', new SensibleCommentBody()],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        StartupComment::create([
            'startup_id' => $startup->id,
            'user_id' => $user->id,
            'body' => trim($request->input('body')),
        ]);

        return redirect()->back()->with('success', 'Comment posted.');
    }
}
