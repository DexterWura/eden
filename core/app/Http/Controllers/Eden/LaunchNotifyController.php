<?php

namespace App\Http\Controllers\Eden;

use App\Models\LaunchNotification;
use App\Models\Startup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LaunchNotifyController extends EdenController
{
    public function show(string $slug)
    {
        $startup = Startup::where('slug', $slug)->firstOrFail();
        if ($startup->isActive()) {
            return $this->page('launch-notify-already', $startup->name . ' – Already live', null, [
                'startup' => $startup,
            ]);
        }
        return $this->page('launch-notify', 'Notify me when ' . $startup->name . ' launches', null, [
            'startup' => $startup,
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $startup = Startup::where('slug', $slug)->firstOrFail();
        if ($startup->isActive()) {
            return redirect()->route('startup.show', $startup->slug)->with('info', 'This app is already live.');
        }
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = strtolower(trim($request->input('email')));
        LaunchNotification::firstOrCreate(
            ['email' => $email, 'startup_id' => $startup->id]
        );
        return redirect()->route('launch-notify.show', $startup->slug)->with('success', "We'll email you when {$startup->name} launches.");
    }
}
