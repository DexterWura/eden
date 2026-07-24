<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Services\StartupService;
use Illuminate\Http\Response;

class BadgesController extends Controller
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function index(): Response
    {
        $user = auth()->user();
        $startups = Startup::visibleToUser($user)->active()->orderBy('name')->get();
        $productOfDayId = $this->startupService->getProductOfDayId();
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';

        $content = view('eden.founder.badges', [
            'startups' => $startups,
            'productOfDayId' => $productOfDayId,
            'siteName' => $siteName,
            'badgeBaseUrl' => url('/badge'),
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Badges',
            'sidebar' => 'founder',
            'activeNav' => 'badges',
            'dashboardLogo' => $siteName,
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'notifyPartial' => view('partials.notify')->render(),
            'content' => $content,
        ]);
    }
}
