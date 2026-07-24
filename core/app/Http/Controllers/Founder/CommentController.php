<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\StartupComment;
use App\Rules\SensibleCommentBody;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $startupIds = Startup::visibleToUser($user)->pluck('id');
        $comments = StartupComment::query()
            ->whereIn('startup_id', $startupIds)
            ->with(['startup:id,name,slug', 'user:id,name', 'founderResponder:id,name'])
            ->latest()
            ->paginate(20);

        $content = view('eden.founder.comments', ['comments' => $comments])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Comments',
            'sidebar' => 'founder',
            'activeNav' => 'comments',
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => $user->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr($user->name ?? '?', 0, 1)),
            'notifyPartial' => view('partials.notify')->render(),
            'content' => $content,
        ]);
    }

    public function reply(Request $request, StartupComment $comment): RedirectResponse
    {
        $this->authorize('manage', $comment->startup);
        $validated = $request->validate([
            'reply' => ['required', 'string', 'min:1', 'max:2000', new SensibleCommentBody()],
        ]);

        $comment->update([
            'founder_reply' => trim($validated['reply']),
            'founder_replied_by' => $request->user()->id,
            'founder_replied_at' => now(),
            'addressed_at' => now(),
        ]);

        return back()->with('notify', [['success', 'Founder reply published.']]);
    }

    public function setAddressed(Request $request, StartupComment $comment): RedirectResponse
    {
        $this->authorize('manage', $comment->startup);
        $validated = $request->validate(['addressed' => ['required', 'boolean']]);
        $comment->update(['addressed_at' => $validated['addressed'] ? now() : null]);

        return back()->with('notify', [['success', $validated['addressed'] ? 'Comment marked addressed.' : 'Comment reopened.']]);
    }
}
