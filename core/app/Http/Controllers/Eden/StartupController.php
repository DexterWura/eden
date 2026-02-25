<?php

namespace App\Http\Controllers\Eden;

use App\Services\StartupService;

class StartupController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function launchingToday()
    {
        $startups = $this->startupService->getLaunchingToday();
        return $this->page('launching-today', 'Launching today', 'scripts-launching-today', [
            'startups' => $startups,
        ]);
    }

    public function show(string $slug)
    {
        $startup = $this->startupService->getBySlug($slug);
        return $this->page('startup-show', $startup->name, null, ['startup' => $startup]);
    }
}
