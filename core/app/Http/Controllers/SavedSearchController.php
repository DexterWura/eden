<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SavedSearchController extends Controller
{
    public function index()
    {
        $pageTitle = 'Saved Searches';
        $user = auth()->user();

        $savedSearches = SavedSearch::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.saved_search.index', compact('pageTitle', 'savedSearches'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email_alerts' => 'nullable|boolean',
            'alert_frequency' => 'nullable|in:instant,daily,weekly',
        ]);

        try {
            // Get all filter parameters from request
            $filters = [
                'search' => $request->search,
                'business_type' => $request->business_type,
                'sale_type' => $request->sale_type,
                'category' => $request->category,
                'min_price' => $request->min_price,
                'max_price' => $request->max_price,
                'min_revenue' => $request->min_revenue,
                'max_revenue' => $request->max_revenue,
                'min_traffic' => $request->min_traffic,
                'max_traffic' => $request->max_traffic,
                'min_age' => $request->min_age,
                'max_age' => $request->max_age,
                'verified' => $request->verified,
                'featured' => $request->featured,
                'monetization' => $request->monetization,
                'traffic_source' => $request->traffic_source,
                'sort' => $request->sort,
            ];

            // Remove empty filters
            $filters = array_filter($filters, function ($value) {
                return $value !== null && $value !== '';
            });

            $savedSearch = new SavedSearch();
            $savedSearch->user_id = $user->id;
            $savedSearch->name = $request->name;
            $savedSearch->filters = $filters;
            $savedSearch->email_alerts = $request->email_alerts ?? false;
            $savedSearch->alert_frequency = $request->alert_frequency ?? 'daily';
            $savedSearch->save();

            $notify[] = ['success', 'Search saved successfully'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            Log::error('Save search failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to save search. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function apply($id)
    {
        $user = auth()->user();
        $savedSearch = SavedSearch::where('user_id', $user->id)->findOrFail($id);

        // Build query string from filters
        $queryParams = array_merge(
            $savedSearch->filters,
            ['saved_search' => $id]
        );

        return redirect()->route('marketplace.browse', $queryParams);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $savedSearch = SavedSearch::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email_alerts' => 'nullable|boolean',
            'alert_frequency' => 'nullable|in:instant,daily,weekly',
        ]);

        try {
            $savedSearch->name = $request->name;
            $savedSearch->email_alerts = $request->email_alerts ?? false;
            $savedSearch->alert_frequency = $request->alert_frequency ?? 'daily';
            $savedSearch->save();

            $notify[] = ['success', 'Search updated successfully'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            Log::error('Update search failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to update search. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $savedSearch = SavedSearch::where('user_id', $user->id)->findOrFail($id);

        try {
            $savedSearch->delete();

            $notify[] = ['success', 'Search deleted successfully'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            Log::error('Delete search failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to delete search. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function toggleAlerts($id)
    {
        $user = auth()->user();
        $savedSearch = SavedSearch::where('user_id', $user->id)->findOrFail($id);

        try {
            $savedSearch->email_alerts = !$savedSearch->email_alerts;
            $savedSearch->save();

            $message = $savedSearch->email_alerts ? 'Email alerts enabled' : 'Email alerts disabled';
            $notify[] = ['success', $message];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            Log::error('Toggle alerts failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to update alerts. Please try again.'];
            return back()->withNotify($notify);
        }
    }
}

