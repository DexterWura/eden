<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSpot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdSpotController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->get('status', '');
        $placement = $request->get('placement', '');

        $query = AdSpot::query()->orderByDesc('created_at');

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($placement !== '') {
            $query->where('placement', $placement);
        }

        $ads = $query->paginate(20)->withQueryString();

        $content = view('eden.ad-spots.index', [
            'ads' => $ads,
            'status' => $status,
            'placement' => $placement,
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Ad spots', 'ad-spots', $content));
    }

    public function expire(AdSpot $ad): RedirectResponse
    {
        $ad->status = AdSpot::STATUS_EXPIRED;
        $ad->ends_at = $ad->ends_at ?? now();
        $ad->save();

        return redirect()->route('admin.ad-spots.index')->with('notify', [['success', 'Ad spot marked as expired.']]);
    }

    public function activate(AdSpot $ad): RedirectResponse
    {
        $ad->status = AdSpot::STATUS_ACTIVE;
        $ad->starts_at = $ad->starts_at ?? now();
        $ad->ends_at = $ad->ends_at ?? now()->addMonth();
        $ad->save();

        return redirect()->route('admin.ad-spots.index')->with('notify', [['success', 'Ad spot activated.']]);
    }

    private function dashboardVars(string $title, string $activeNav, string $content): array
    {
        return [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ];
    }
}

