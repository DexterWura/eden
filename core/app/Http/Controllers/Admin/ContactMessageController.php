<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        if ($admin) {
            $admin->last_saw_contact_messages_at = now();
            $admin->save();
        }

        $messages = ContactSubmission::query()
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $content = view('eden.contact-messages.index', ['messages' => $messages])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Contact messages', 'contact-messages', $content));
    }

    public function show(ContactSubmission $submission)
    {
        $admin = auth()->guard('admin')->user();
        if ($admin) {
            $admin->last_saw_contact_messages_at = now();
            $admin->save();
        }

        $defaultSubject = $submission->reply_subject ?: 'Re: ' . ($submission->subject ?: 'Your message to Eden');
        $defaultBody = $submission->reply_body ?: "Hi {$submission->name},\n\nThanks for reaching out.\n\n";

        $content = view('eden.contact-messages.show', [
            'message' => $submission,
            'defaultSubject' => $defaultSubject,
            'defaultBody' => $defaultBody,
            'templatePlaceholder' => '{{message}}',
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Contact message', 'contact-messages', $content));
    }

    public function reply(Request $request, ContactSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $user = [
            'username' => $submission->email,
            'email' => $submission->email,
            'fullname' => $submission->name ?: $submission->email,
        ];

        notify($user, 'DEFAULT', [
            'subject' => $validated['subject'],
            'message' => $validated['body'],
        ], ['email'], false);

        $submission->reply_subject = $validated['subject'];
        $submission->reply_body = $validated['body'];
        $submission->replied_at = now();
        $submission->save();

        return redirect()
            ->route('admin.contact-messages.show', $submission)
            ->with('notify', [['success', 'Reply sent to ' . $submission->email . '.']]);
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
