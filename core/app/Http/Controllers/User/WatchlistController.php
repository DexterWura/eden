<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index()
    {
        $pageTitle = 'My Watchlist';
        $user = auth()->user();

        $watchlist = Watchlist::where('user_id', $user->id)
            ->with(['listing.images', 'listing.seller', 'listing.bids'])
            ->whereHas('listing', function ($q) {
                $q->where('status', Status::LISTING_ACTIVE);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.watchlist.index', compact('pageTitle', 'watchlist'));
    }

    public function toggle(Request $request, $listingId)
    {
        $user = auth()->user();
        $listing = Listing::active()->findOrFail($listingId);

        $watchlist = Watchlist::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($watchlist) {
            $watchlist->delete();
            $listing->decrement('watchlist_count');
            $message = 'Removed from watchlist';
            $watching = false;
        } else {
            Watchlist::create([
                'user_id' => $user->id,
                'listing_id' => $listing->id,
                'notify_bid' => true,
                'notify_price_change' => true,
                'notify_ending' => true,
            ]);
            $listing->increment('watchlist_count');
            $message = 'Added to watchlist';
            $watching = true;
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'watching' => $watching,
                'count' => $listing->fresh()->watchlist_count,
            ]);
        }

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function updateSettings(Request $request, $id)
    {
        $user = auth()->user();
        $watchlist = Watchlist::where('user_id', $user->id)->findOrFail($id);

        $watchlist->notify_bid = $request->boolean('notify_bid');
        $watchlist->notify_price_change = $request->boolean('notify_price_change');
        $watchlist->notify_ending = $request->boolean('notify_ending');
        $watchlist->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Settings updated']);
        }

        $notify[] = ['success', 'Watchlist settings updated'];
        return back()->withNotify($notify);
    }

    public function remove($id)
    {
        $user = auth()->user();
        $watchlist = Watchlist::where('user_id', $user->id)->findOrFail($id);

        $listing = $watchlist->listing;
        if ($listing) {
            $listing->decrement('watchlist_count');
        }

        $watchlist->delete();

        $notify[] = ['success', 'Removed from watchlist'];
        return back()->withNotify($notify);
    }
}

