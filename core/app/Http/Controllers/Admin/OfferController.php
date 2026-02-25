<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All Offers';
        $offers = $this->getOffers($request)->paginate(getPaginate());
        return view('admin.offer.index', compact('pageTitle', 'offers'));
    }

    public function pending(Request $request)
    {
        $pageTitle = 'Pending Offers';
        $offers = $this->getOffers($request)->where('status', Status::OFFER_PENDING)->paginate(getPaginate());
        return view('admin.offer.index', compact('pageTitle', 'offers'));
    }

    public function accepted(Request $request)
    {
        $pageTitle = 'Accepted Offers';
        $offers = $this->getOffers($request)->where('status', Status::OFFER_ACCEPTED)->paginate(getPaginate());
        return view('admin.offer.index', compact('pageTitle', 'offers'));
    }

    public function details($id)
    {
        $pageTitle = 'Offer Details';
        $offer = Offer::with(['listing.images', 'buyer', 'seller', 'escrow'])->findOrFail($id);
        return view('admin.offer.details', compact('pageTitle', 'offer'));
    }

    public function cancel($id)
    {
        $offer = Offer::whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->findOrFail($id);

        $offer->status = Status::OFFER_CANCELLED;
        $offer->save();

        // Notify both parties
        notify($offer->buyer, 'OFFER_CANCELLED_ADMIN', [
            'listing_title' => $offer->listing->title,
            'listing_url' => url(route('marketplace.listing.show', $offer->listing->slug)),
            'offer_amount' => showAmount($offer->amount),
        ]);

        notify($offer->seller, 'OFFER_CANCELLED_ADMIN', [
            'listing_title' => $offer->listing->title,
            'listing_url' => url(route('marketplace.listing.show', $offer->listing->slug)),
            'offer_amount' => showAmount($offer->amount),
        ]);

        $notify[] = ['success', 'Offer cancelled successfully'];
        return back()->withNotify($notify);
    }

    private function getOffers($request)
    {
        return Offer::with(['listing', 'buyer', 'seller'])
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('offer_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('buyer', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('seller', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('listing', function ($q) use ($search) {
                            $q->where('title', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc');
    }
}

