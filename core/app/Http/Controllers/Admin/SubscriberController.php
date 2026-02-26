<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscriber::query()->orderByDesc('created_at');
        $search = $request->get('q', '');
        if ($search !== '') {
            $query->where('email', 'like', '%' . $search . '%');
        }
        $subscribers = $query->paginate(30)->withQueryString();

        $content = view('eden.subscribers.index', [
            'subscribers' => $subscribers,
            'search' => $search,
            'total' => Subscriber::count(),
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Subscribers', 'subscribers', $content));
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();
        return redirect()->route('admin.subscribers.index')
            ->with('notify', [['success', 'Subscriber removed.']]);
    }

    private function dashboardVars(string $title, string $activeNav, string $content): array
    {
        return [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ];
    }
}
