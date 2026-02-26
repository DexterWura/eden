<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

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

    public function import()
    {
        $content = view('eden.subscribers.import')->render();
        return response()->view('eden.layout-dashboard', $this->dashboardVars('Import subscribers', 'subscribers', $content));
    }

    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        if (count($rows) === 0) {
            return redirect()->route('admin.subscribers.import')
                ->with('notify', [['error', 'File is empty or invalid.']]);
        }

        $header = array_shift($rows);
        $emailColumn = null;
        foreach ($header as $i => $col) {
            if (stripos($col, 'email') !== false) {
                $emailColumn = $i;
                break;
            }
        }
        if ($emailColumn === null) {
            $emailColumn = 0;
        }

        $added = 0;
        $skipped = 0;
        $existingEmails = Subscriber::pluck('email')->flip()->all();

        foreach ($rows as $row) {
            $email = isset($row[$emailColumn]) ? trim($row[$emailColumn]) : '';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }
            $email = strtolower($email);
            if (isset($existingEmails[$email])) {
                $skipped++;
                continue;
            }
            Subscriber::create(['email' => $email]);
            $existingEmails[$email] = true;
            $added++;
        }

        $msg = $added > 0
            ? "Imported {$added} subscriber(s)." . ($skipped > 0 ? " {$skipped} row(s) skipped (invalid or duplicate)." : '')
            : 'No new subscribers added.' . ($skipped > 0 ? " {$skipped} row(s) skipped." : '');
        return redirect()->route('admin.subscribers.index')
            ->with('notify', [[$added > 0 ? 'success' : 'info', $msg]]);
    }

    public function compose()
    {
        $content = view('eden.subscribers.compose')->render();
        return response()->view('eden.layout-dashboard', $this->dashboardVars('Compose email', 'subscribers', $content));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
        ]);
        $subject = $request->input('subject');
        $body = $request->input('body');
        $html = view('eden.subscribers.email-preview', compact('subject', 'body'))->render();
        return response()->json(['html' => $html]);
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'audience' => ['required', Rule::in('founders', 'subscribers', 'all')],
        ]);

        $emails = $this->resolveAudienceEmails($request->input('audience'));
        if ($emails->isEmpty()) {
            return redirect()->route('admin.subscribers.compose')
                ->withInput()
                ->with('notify', [['error', 'No recipients for the selected audience.']]);
        }

        $subject = $request->input('subject');
        $body = $request->input('body');
        $html = view('eden.subscribers.email-preview', compact('subject', 'body'))->render();

        foreach ($emails as $email) {
            try {
                Mail::html($html, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            } catch (\Exception $e) {
                return redirect()->route('admin.subscribers.compose')
                    ->withInput()
                    ->with('notify', [['error', 'Failed to send: ' . $e->getMessage()]]);
            }
        }

        return redirect()->route('admin.subscribers.compose')
            ->with('notify', [['success', 'Email sent to ' . $emails->count() . ' recipient(s).']]);
    }

    private function resolveAudienceEmails(string $audience): \Illuminate\Support\Collection
    {
        if ($audience === 'founders') {
            return User::has('startups')->pluck('email')->unique()->filter()->values();
        }
        if ($audience === 'subscribers') {
            return Subscriber::pluck('email');
        }
        $founders = User::has('startups')->pluck('email');
        $subs = Subscriber::pluck('email');
        return $founders->merge($subs)->unique()->filter()->values();
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
