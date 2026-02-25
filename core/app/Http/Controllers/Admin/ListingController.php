<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Jobs\SendSavedSearchAlertsJob;
use App\Models\Listing;
use App\Models\ListingCategory;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All Listings';
        $listings = $this->getListings($request)->paginate(getPaginate())->appends($request->all());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function pending(Request $request)
    {
        $pageTitle = 'Pending Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_PENDING);
        $listings = $listings->paginate(getPaginate())->appends($request->all());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function active(Request $request)
    {
        $pageTitle = 'Active Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_ACTIVE);
        $listings = $listings->paginate(getPaginate())->appends($request->all());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function sold(Request $request)
    {
        $pageTitle = 'Sold Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_SOLD);
        $listings = $listings->paginate(getPaginate())->appends($request->all());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function rejected(Request $request)
    {
        $pageTitle = 'Rejected Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_REJECTED);
        $listings = $listings->paginate(getPaginate())->appends($request->all());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function expired(Request $request)
    {
        $pageTitle = 'Expired Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_EXPIRED);
        $listings = $listings->paginate(getPaginate())->appends($request->all());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function details($id)
    {
        $pageTitle = 'Listing Details';
        $listing = Listing::with([
            'user',
            'images',
            'listingCategory',
            'metrics',
            'bids.user',
            'offers.buyer',
            'questions.asker',
            'escrow',
            'winner',
        ])->findOrFail($id);

        return view('admin.listing.details', compact('pageTitle', 'listing'));
    }

    public function approve($id)
    {
        $listing = Listing::where('status', Status::LISTING_PENDING)->findOrFail($id);


        $listing->status = Status::LISTING_ACTIVE;
        $listing->approved_at = now();

        // Set auction times if it's an auction
        if ($listing->sale_type === 'auction') {
            $listing->auction_start = now();
            
            // Get configured min/max auction days
            $minDays = \App\Models\MarketplaceSetting::minAuctionDays();
            $maxDays = \App\Models\MarketplaceSetting::maxAuctionDays();
            
            // Use stored duration, but validate against configured limits
            $duration = $listing->auction_duration_days ?? $minDays;
            
            // Ensure duration is within configured limits
            if ($duration < $minDays) {
                $duration = $minDays;
            } elseif ($duration > $maxDays) {
                $duration = $maxDays;
            }
            
            $listing->auction_end = now()->addDays($duration);
        }

        $listing->save();

        admin_audit_log('listing.approved', "Listing approved: {$listing->listing_number} - {$listing->title}", $listing);

        // Notify seller
        notify($listing->user, 'LISTING_APPROVED', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
        ]);

        SendSavedSearchAlertsJob::dispatch($listing->id);

        $notify[] = ['success', 'Listing approved successfully'];
        return back()->withNotify($notify);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $listing = Listing::where('status', Status::LISTING_PENDING)->findOrFail($id);

        $listing->status = Status::LISTING_REJECTED;
        $listing->rejection_reason = $request->reason;
        $listing->save();

        admin_audit_log('listing.rejected', "Listing rejected: {$listing->listing_number} - {$listing->title}. Reason: {$request->reason}", $listing, [], ['reason' => $request->reason]);

        // Notify seller (pass reason under all possible shortcode names so template is replaced)
        notify($listing->user, 'LISTING_REJECTED', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
            'reason' => $request->reason,
            'rejection_reason' => $request->reason,
            'rejection reason' => $request->reason,
        ]);

        $notify[] = ['success', 'Listing rejected'];
        return back()->withNotify($notify);
    }

    public function feature(Request $request, $id)
    {
        $listing = Listing::active()->findOrFail($id);

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $listing->is_featured = true;
        $listing->featured_until = now()->addDays($request->days);
        $listing->save();

        $notify[] = ['success', 'Listing featured for ' . $request->days . ' days'];
        return back()->withNotify($notify);
    }

    public function unfeature($id)
    {
        $listing = Listing::findOrFail($id);

        $listing->is_featured = false;
        $listing->featured_until = null;
        $listing->save();

        $notify[] = ['success', 'Listing unfeatured'];
        return back()->withNotify($notify);
    }

    public function verify(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);

        $listing->is_verified = $request->boolean('is_verified', true);
        $listing->revenue_verified = $request->boolean('revenue_verified');
        $listing->traffic_verified = $request->boolean('traffic_verified');
        $listing->verification_notes = $request->verification_notes;
        $listing->save();

        $notify[] = ['success', 'Listing verification updated'];
        return back()->withNotify($notify);
    }

    public function extendAuction(Request $request, $id)
    {
        $listing = Listing::activeAuctions()->findOrFail($id);

        $request->validate([
            'hours' => 'required|integer|min:1|max:720',
        ]);

        $listing->auction_end = $listing->auction_end->addHours($request->hours);
        $listing->save();

        $notify[] = ['success', 'Auction extended by ' . $request->hours . ' hours'];
        return back()->withNotify($notify);
    }

    public function cancel(Request $request, $id)
    {
        $listing = Listing::whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
            ->findOrFail($id);

        // If listing is active (running), require a reason
        if ($listing->status === Status::LISTING_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:1000',
            ]);
        }

        $oldStatus = $listing->status;
        $listing->status = Status::LISTING_CANCELLED;
        if ($request->filled('reason')) {
            $listing->rejection_reason = $request->reason;
        }
        $listing->save();

        admin_audit_log('listing.cancelled', "Listing cancelled: {$listing->listing_number} - {$listing->title}" . ($request->filled('reason') ? ". Reason: {$request->reason}" : ''), $listing, ['status' => $oldStatus], ['status' => Status::LISTING_CANCELLED, 'reason' => $request->reason ?? null]);

        // Notify seller
        notify($listing->user, 'LISTING_CANCELLED_ADMIN', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
            'reason' => $request->reason ?? ($oldStatus === Status::LISTING_ACTIVE ? 'No reason provided' : null),
        ]);

        $notify[] = ['success', 'Listing cancelled successfully'];
        return back()->withNotify($notify);
    }

    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $listing = Listing::where('status', Status::LISTING_ACTIVE)
            ->where('is_deactivated', false)
            ->findOrFail($id);

        $listing->is_deactivated = true;
        $listing->deactivation_reason = $request->reason;
        $listing->deactivated_at = now();
        $listing->save();

        admin_audit_log('listing.deactivated', "Listing deactivated: {$listing->listing_number} - {$listing->title}. Reason: {$request->reason}", $listing, ['is_deactivated' => false], ['is_deactivated' => true, 'deactivation_reason' => $request->reason]);

        // Notify seller
        notify($listing->user, 'LISTING_DEACTIVATED', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
            'reason' => $request->reason,
        ]);

        $notify[] = ['success', 'Listing deactivated successfully'];
        return back()->withNotify($notify);
    }

    public function reactivate($id)
    {
        $listing = Listing::where('status', Status::LISTING_ACTIVE)
            ->where('is_deactivated', true)
            ->findOrFail($id);

        $oldReason = $listing->deactivation_reason;
        $listing->is_deactivated = false;
        $listing->deactivation_reason = null;
        $listing->deactivated_at = null;
        $listing->save();

        admin_audit_log('listing.reactivated', "Listing reactivated: {$listing->listing_number} - {$listing->title}", $listing, ['is_deactivated' => true, 'deactivation_reason' => $oldReason], ['is_deactivated' => false, 'deactivation_reason' => null]);

        // Notify seller
        notify($listing->user, 'LISTING_REACTIVATED', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
        ]);

        $notify[] = ['success', 'Listing reactivated successfully'];
        return back()->withNotify($notify);
    }

    private function getListings($request)
    {
        return Listing::with(['user', 'listingCategory', 'images'])
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('listing_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->sale_type, function ($q, $type) {
                return $q->where('sale_type', $type);
            })
            ->when($request->filled('featured'), function ($q) use ($request) {
                if ($request->featured === 'active') {
                    return $q->where('is_featured', true)->where('featured_until', '>', now());
                }
                if ($request->featured === 'yes') {
                    return $q->where('is_featured', true);
                }
                if ($request->featured === 'no') {
                    return $q->where(function ($sub) {
                        $sub->where('is_featured', false)
                            ->orWhereNull('featured_until')
                            ->orWhere('featured_until', '<=', now());
                    });
                }

                return $q;
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc');
    }
}

