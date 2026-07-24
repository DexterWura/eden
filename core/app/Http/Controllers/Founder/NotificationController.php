<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $notifications = $user->notifications()
            ->latest()
            ->paginate(15);

        $content = view('eden.founder.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Notifications',
            'sidebar' => 'founder',
            'activeNav' => 'notifications',
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'notifyPartial' => view('partials.notify')->render(),
            'content' => $content,
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $ownedNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $ownedNotification->markAsRead();

        return back()->with('notify', [['success', 'Notification marked as read.']]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('notify', [['success', 'All notifications marked as read.']]);
    }
}
