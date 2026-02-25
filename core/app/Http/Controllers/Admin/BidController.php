<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All Bids';
        $bids = $this->getBids($request)->paginate(getPaginate());
        return view('admin.bid.index', compact('pageTitle', 'bids'));
    }

    public function winning(Request $request)
    {
        $pageTitle = 'Winning Bids';
        $bids = $this->getBids($request)->where('status', Status::BID_WINNING)->paginate(getPaginate());
        return view('admin.bid.index', compact('pageTitle', 'bids'));
    }

    public function won(Request $request)
    {
        $pageTitle = 'Won Bids';
        $bids = $this->getBids($request)->where('status', Status::BID_WON)->paginate(getPaginate());
        return view('admin.bid.index', compact('pageTitle', 'bids'));
    }

    public function details($id)
    {
        $pageTitle = 'Bid Details';
        $bid = Bid::with(['listing.images', 'listing.seller', 'user'])->findOrFail($id);
        return view('admin.bid.details', compact('pageTitle', 'bid'));
    }

    public function cancel($id)
    {
        $bid = Bid::whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
            ->with('listing')
            ->findOrFail($id);

        $wasWinning = $bid->status === Status::BID_WINNING;
        $listing = $bid->listing;

        $bid->status = Status::BID_CANCELLED;
        $bid->save();

        // If was winning, find new highest
        if ($wasWinning) {
            $newHighest = Bid::where('listing_id', $listing->id)
                ->whereIn('status', [Status::BID_ACTIVE, Status::BID_OUTBID])
                ->orderBy('amount', 'desc')
                ->first();

            if ($newHighest) {
                $newHighest->status = Status::BID_WINNING;
                $newHighest->save();

                $listing->current_bid = $newHighest->amount;
                $listing->highest_bidder_id = $newHighest->user_id;
            } else {
                $listing->current_bid = 0;
                $listing->highest_bidder_id = 0;
            }

            $listing->total_bids = $listing->bids()->whereNotIn('status', [Status::BID_CANCELLED])->count();
            $listing->save();
        }

        // Notify bidder
        notify($bid->user, 'BID_CANCELLED_ADMIN', [
            'listing_title' => $listing->title,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
            'bid_amount' => showAmount($bid->amount),
        ]);

        $notify[] = ['success', 'Bid cancelled successfully'];
        return back()->withNotify($notify);
    }

    public function processAuctionEnd($listingId)
    {
        try {
            // Lock listing to prevent concurrent processing
            $listing = Listing::lockForUpdate()
                ->activeAuctions()
                ->where('auction_end', '<=', now())
                ->findOrFail($listingId);

            // Check if already processed
            if ($listing->status === Status::LISTING_SOLD || $listing->status === Status::LISTING_EXPIRED) {
                $notify[] = ['error', 'This auction has already been processed'];
                return back()->withNotify($notify);
            }

            DB::beginTransaction();
            
            try {
                $winningBid = Bid::where('listing_id', $listing->id)
                    ->where('status', Status::BID_WINNING)
                    ->with('user')
                    ->first();

                if (!$winningBid) {
                    // No bids - mark as expired
                    $listing->status = Status::LISTING_EXPIRED;
                    $listing->save();

                    DB::commit();

                    notify($listing->user, 'AUCTION_ENDED_NO_BIDS', [
                        'listing_title' => $listing->title,
                        'listing_number' => $listing->listing_number,
                        'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    ]);

                    $notify[] = ['success', 'Auction ended with no bids'];
                    return back()->withNotify($notify);
                }

                // Check if reserve was met
                if ($listing->reserve_price > 0 && $listing->current_bid < $listing->reserve_price) {
                    $listing->status = Status::LISTING_EXPIRED;
                    $listing->save();

                    // Mark all bids as lost
                    Bid::where('listing_id', $listing->id)
                        ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING, Status::BID_OUTBID])
                        ->update(['status' => Status::BID_LOST]);

                    DB::commit();

                    notify($listing->user, 'AUCTION_ENDED_RESERVE_NOT_MET', [
                        'listing_title' => $listing->title,
                        'listing_number' => $listing->listing_number,
                        'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                        'highest_bid' => showAmount($listing->current_bid),
                        'reserve_price' => showAmount($listing->reserve_price),
                    ]);

                    $notify[] = ['success', 'Auction ended - reserve not met'];
                    return back()->withNotify($notify);
                }

                // Winner found
                $winningBid->status = Status::BID_WON;
                $winningBid->save();

                // Mark other bids as lost
                Bid::where('listing_id', $listing->id)
                    ->where('id', '!=', $winningBid->id)
                    ->whereIn('status', [Status::BID_ACTIVE, Status::BID_OUTBID])
                    ->update(['status' => Status::BID_LOST]);

                // Update listing - don't mark as SOLD yet, just set escrow_id to hide from public
                // Keep status as LISTING_ACTIVE - it will be hidden from public because escrow_id is set
                $listing->winner_id = $winningBid->user_id;
                $listing->final_price = $winningBid->amount;
                // Don't set sold_at yet - will be set when escrow is completed

                // Create escrow
                $escrow = $this->createEscrow($listing, $winningBid->user, $winningBid->amount);
                $listing->escrow_id = $escrow->id;
                $listing->save();

                // Don't update user stats yet - will be updated when escrow is completed

                DB::commit();

                // Notify winner (outside transaction) - both email and database notification
                notify($winningBid->user, 'AUCTION_WON', [
                    'listing_title' => $listing->title,
                    'listing_number' => $listing->listing_number,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'winning_bid' => showAmount($winningBid->amount),
                    'seller_username' => $listing->seller->username ?? $listing->seller->name ?? 'Seller',
                    'escrow_number' => $escrow->escrow_number,
                    'escrow_id' => $escrow->id,
                    'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                    'action_required' => 'You can pay the full amount or set up milestones for this escrow',
                ]);

                // Send database notification to winner for dashboard
                $winningBid->user->notify(new \App\Notifications\AuctionWon(
                    $listing,
                    showAmount($winningBid->amount),
                    $escrow->escrow_number,
                    $escrow->id
                ));

                // Notify seller (outside transaction) - both email and database notification
                notify($listing->user, 'AUCTION_ENDED_SOLD', [
                    'listing_title' => $listing->title,
                    'listing_number' => $listing->listing_number,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'final_price' => showAmount($winningBid->amount),
                    'winner' => $winningBid->user->username ?? $winningBid->user->name,
                    'winner_username' => $winningBid->user->username ?? $winningBid->user->name,
                    'escrow_number' => $escrow->escrow_number,
                    'escrow_id' => $escrow->id,
                    'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                ]);

                // Send database notification to seller for dashboard
                $listing->seller->notify(new \App\Notifications\AuctionEndedSold(
                    $listing,
                    showAmount($winningBid->amount),
                    $winningBid->user->username,
                    $escrow->escrow_number,
                    $escrow->id
                ));

                $notify[] = ['success', 'Auction processed successfully'];
                return back()->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auction processing failed: ' . $e->getMessage(), [
                    'listing_id' => $listingId,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Auction processing error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while processing the auction. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    private function getBids($request)
    {
        return Bid::with(['listing', 'user'])
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('bid_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('listing', function ($q) use ($search) {
                            $q->where('title', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc');
    }

    private function createEscrow($listing, $buyer, $amount)
    {
        $seller = $listing->seller;
        $paymentMode = ($listing->payout_method ?? 'system') === 'direct' ? 'direct' : 'system';
        $feeBreakdown = app(\App\Services\EscrowFeeCalculator::class)->calculateMarketplaceFees((float) $amount, 'escrow_service_fee', $paymentMode);
        $charge = (float) ($feeBreakdown['total'] ?? 0);
        $buyerCharge = (float) ($feeBreakdown['buyer'] ?? $charge);
        $sellerCharge = (float) ($feeBreakdown['seller'] ?? 0);
        $chargePayer = $buyerCharge > 0 && $sellerCharge > 0 ? 3 : ($sellerCharge > 0 ? Status::CHARGE_PAYER_SELLER : Status::CHARGE_PAYER_BUYER);

        $escrow = new \App\Models\Escrow();
        $escrow->escrow_number = getTrx();
        $escrow->seller_id = $seller->id;
        $escrow->buyer_id = $buyer->id;
        $escrow->creator_id = $buyer->id;
        if (($listing->payout_method ?? 'system') === 'direct') {
            $escrow->payment_mode = 'direct';
            $escrow->external_amount = $amount;
            $escrow->external_payment_link = $listing->direct_payment_link;
            $escrow->amount = 0; // platform does not hold the sale amount
        } else {
            $escrow->payment_mode = 'system';
            $escrow->external_amount = 0;
            $escrow->external_payment_link = null;
            $escrow->amount = $amount;
        }
        $escrow->charge = $charge;
        $escrow->buyer_charge = $buyerCharge;
        $escrow->seller_charge = $sellerCharge;
        $escrow->charge_payer = $chargePayer;
        $escrow->title = 'Auction Won: ' . $listing->title;
        $escrow->details = "Escrow for auction: {$listing->title}\nListing #: {$listing->listing_number}";
        $escrow->status = Status::ESCROW_ACCEPTED;
        $escrow->save();

        $conversation = new \App\Models\Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;
        $conversation->save();

        return $escrow;
    }
}

