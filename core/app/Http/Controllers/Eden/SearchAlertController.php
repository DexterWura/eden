<?php

namespace App\Http\Controllers\Eden;

use App\Models\SearchAlertSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchAlertController extends EdenController
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return redirect()->to(url('/'));
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'search_query' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $q = isset($validated['search_query']) ? trim($validated['search_query']) : '';
        $category = isset($validated['category']) ? trim((string) $validated['category']) : '';
        $location = isset($validated['location']) ? trim((string) $validated['location']) : '';

        if ($q === '' && $category === '' && $location === '') {
            return redirect()->back()->withInput()->withErrors([
                'search_query' => __('Add a search keyword, category, or location first, then save this alert.'),
            ]);
        }

        $email = mb_strtolower(trim($validated['email']));
        $criteriaHash = SearchAlertSubscription::hashCriteria(
            $q !== '' ? $q : null,
            $category !== '' ? $category : null,
            $location !== '' ? $location : null
        );

        $existing = SearchAlertSubscription::query()
            ->where('email', $email)
            ->where('criteria_hash', $criteriaHash)
            ->first();

        if ($existing) {
            return redirect()->back()->with('success', __('You are already subscribed to alerts for these filters.'));
        }

        SearchAlertSubscription::create([
            'user_id' => Auth::id(),
            'email' => $email,
            'search_query' => $q !== '' ? $q : null,
            'category' => $category !== '' ? $category : null,
            'location' => $location !== '' ? $location : null,
            'criteria_hash' => $criteriaHash,
            'last_notified_at' => now(),
        ]);

        return redirect()->back()->with('success', __('We will email you when new listings match your search. Unsubscribe anytime from the link in the email.'));
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        $sub = SearchAlertSubscription::query()->where('unsubscribe_token', $token)->first();
        if (! $sub) {
            return redirect()->to(url('/'))->with('info', __('This alert was already removed or the link is invalid.'));
        }
        $sub->delete();

        return redirect()->to(url('/'))->with('success', __('Search alerts cancelled. You can subscribe again from the homepage anytime.'));
    }
}
