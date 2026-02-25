<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Conversation;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Watchlist;
use App\Models\MarketplaceSetting;
use App\Services\EscrowFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Bids';
        $user = auth()->user();

        $bids = Bid::where('user_id', $user->id)
            ->with(['listing.images', 'listing.seller'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.bid.index', compact('pageTitle', 'bids'));
    }

    public function place(Request $request, $listingId)
    {
        try {
            $user = auth()->user();

            // Use safe database operation wrapper
            return $this->safeDatabaseOperation(function() use ($request, $listingId, $user) {
            
            // Lock listing row to prevent race conditions
            $listing = Listing::lockForUpdate()
                ->active()
                ->auction()
                ->where('auction_end', '>', now())
                ->findOrFail($listingId);

            // Cannot bid on own listing
            if ($listing->user_id === $user->id) {
                $notify[] = ['error', 'You cannot bid on your own listing'];
                return back()->withNotify($notify);
            }

            // Check if user account is fully verified (common sense)
            if (!$user->ev || !$user->sv) {
                $notify[] = ['error', 'Please verify your email and mobile number before placing bids'];
                return back()->withNotify($notify);
            }

            $request->validate([
                'amount' => 'required|numeric|min:' . $listing->minimum_bid,
                'max_bid' => 'nullable|numeric|min:' . $request->amount,
            ]);

            $amount = $request->amount;
            // Set max_bid to 0 if not provided or empty (0 means no max bid), otherwise convert to float
            $maxBid = !empty($request->max_bid) ? (float)$request->max_bid : 0;

            // Validate bid amount - comprehensive business rules
            if ($amount < $listing->minimum_bid) {
                $notify[] = ['error', 'Bid must be at least ' . showAmount($listing->minimum_bid) . ' (minimum bid for this listing)'];
                return back()->withNotify($notify);
            }

            // Check minimum bid increment
            if ($listing->bid_increment > 0 && $listing->current_bid > 0) {
                $minimumBid = $listing->current_bid + $listing->bid_increment;
                if ($amount < $minimumBid) {
                    $notify[] = ['error', 'Bid must be at least ' . showAmount($minimumBid) . ' (current highest bid of ' . showAmount($listing->current_bid) . ' + minimum increment of ' . showAmount($listing->bid_increment) . ')'];
                    return back()->withNotify($notify);
                }
            }

            // Prevent bids that are unreasonably high (potential manipulation)
            $maxReasonableBid = $listing->current_bid > 0 ? $listing->current_bid * 10 : $listing->starting_bid * 100;
            if ($amount > $maxReasonableBid) {
                $notify[] = ['error', 'Bid amount seems unreasonably high. Please contact support if this is a legitimate bid.'];
                return back()->withNotify($notify);
            }

            // Validate reserve price logic
            if ($listing->reserve_price > 0 && $amount < $listing->reserve_price && $listing->current_bid >= $listing->reserve_price) {
                // This shouldn't happen due to minimum_bid logic, but double-check
                $notify[] = ['error', 'Invalid bid amount'];
                return back()->withNotify($notify);
            }

            // Check if auction has ended with buffer time
            if ($listing->auction_end && $listing->auction_end <= now()->addSeconds(5)) {
                $notify[] = ['error', 'This auction has ended'];
                return back()->withNotify($notify);
            }

            // Prevent bidding in the last few seconds (anti-sniping protection)
            // Only block if auction is still active AND time remaining is 30 seconds or less
            if ($listing->auction_end && $listing->auction_end->isFuture()) {
                $secondsRemaining = now()->diffInSeconds($listing->auction_end, false);
                if ($secondsRemaining > 0 && $secondsRemaining <= 30) {
                    $notify[] = ['error', 'Bidding is not allowed in the last 30 seconds of an auction'];
                    return back()->withNotify($notify);
                }
            }

            // Check for existing active bid from this user on this listing
            $existingBid = Bid::where('listing_id', $listingId)
                ->where('user_id', $user->id)
                ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
                ->first();

            if ($existingBid) {
                $notify[] = ['error', 'You already have an active bid on this listing. You can only have one active bid per listing.'];
                return back()->withNotify($notify);
            }

            // Prevent spam bidding - check recent bids from this user
            $recentBidsCount = Bid::where('user_id', $user->id)
                ->where('created_at', '>', now()->subMinutes(5))
                ->count();

            if ($recentBidsCount >= 10) { // Allow max 10 bids per 5 minutes
                $notify[] = ['error', 'You are bidding too frequently. Please wait a moment before placing another bid.'];
                return back()->withNotify($notify);
            }

            // Check if user has been placing bids on too many listings recently (potential bot detection)
            $recentListingsCount = Bid::where('user_id', $user->id)
                ->where('created_at', '>', now()->subMinutes(10))
                ->distinct('listing_id')
                ->count('listing_id');

            if ($recentListingsCount >= 20) { // Max 20 different listings in 10 minutes
                $notify[] = ['error', 'You are bidding on too many listings. Please slow down.'];
                return back()->withNotify($notify);
            }

            // Check user balance (for buy now, not for bids - bids don't require immediate payment)
            // But warn if user might not have sufficient balance for potential win
            $estimatedTotal = $amount + ($amount * 0.15); // Rough estimate including fees
            if ($user->balance < $estimatedTotal && $user->balance < 100) { // Only warn if very low balance
                $notify[] = ['warning', 'Consider adding funds to your account in case you win this auction. Current balance: ' . showAmount($user->balance)];
                // Don't block, just warn
            }

            // Use database transaction for atomicity
            DB::beginTransaction();
            
            try {
                // Mark previous winning bid as outbid
                if ($listing->highest_bidder_id && $listing->highest_bidder_id !== $user->id) {
                    Bid::where('listing_id', $listing->id)
                        ->where('status', Status::BID_WINNING)
                        ->update(['status' => Status::BID_OUTBID]);

                    // Notify outbid user
                    $outbidUser = $listing->highestBidder;
                    if ($outbidUser) {
                        notify($outbidUser, 'BID_OUTBID', [
                            'listing_title' => $listing->title,
                            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                            'your_bid' => showAmount($listing->current_bid),
                            'new_bid' => showAmount($amount),
                            'bidder' => $user->username,
                            'bidder_username' => $user->username,
                        ]);
                    }
                }

                // Create bid
                $bid = new Bid();
                $bid->bid_number = getTrx();
                $bid->listing_id = $listing->id;
                $bid->user_id = $user->id;
                $bid->amount = $amount;
                $bid->max_bid = $maxBid;
                $bid->is_auto_bid = $maxBid > 0;
                $bid->status = Status::BID_WINNING;
                $bid->ip_address = $request->ip();
                $bid->save();

                // Log bid placement
                \Log::info('Bid placed', [
                    'bid_id' => $bid->id,
                    'bid_number' => $bid->bid_number,
                    'listing_id' => $listing->id,
                    'listing_number' => $listing->listing_number,
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'amount' => $amount,
                    'max_bid' => $maxBid,
                    'is_auto_bid' => $maxBid > 0,
                    'previous_highest' => $listing->current_bid - $listing->bid_increment,
                    'current_highest' => $amount,
                    'auction_end' => $listing->auction_end ? $listing->auction_end->toIso8601String() : null,
                    'time_remaining_seconds' => $listing->auction_end ? $listing->auction_end->diffInSeconds(now()) : null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toIso8601String()
                ]);


            // Check for auto-extend on last-minute bids
            $this->checkAutoExtend($listing);

            // Update listing
            $listing->current_bid = $amount;
            $listing->highest_bidder_id = $user->id;
            $listing->total_bids = $listing->bids()->whereNotIn('status', [Status::BID_CANCELLED])->count();
            $listing->save();

                DB::commit();

                // Notify seller (outside transaction)
                notify($listing->seller, 'NEW_BID_RECEIVED', [
                    'listing_title' => $listing->title,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'bid_amount' => showAmount($amount),
                    'bidder' => $user->username,
                    'bidder_username' => $user->username,
                    'current_highest' => showAmount($listing->current_bid),
                ]);
                
                // Send database notification
                $listing->seller->notify(new \App\Notifications\NewBidReceived($listing, showAmount($amount), $user->username));

                // Notify watchlist users (outside transaction)
                $watchlistUsers = Watchlist::where('listing_id', $listing->id)
                    ->where('user_id', '!=', $user->id)
                    ->where('notify_bid', true)
                    ->with('user')
                    ->get();

                foreach ($watchlistUsers as $watch) {
                    if ($watch->user) {
                        notify($watch->user, 'WATCHED_LISTING_NEW_BID', [
                            'listing_title' => $listing->title,
                            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                            'bid_amount' => showAmount($amount),
                        ]);
                    }
                }

                $notify[] = ['success', 'Bid placed successfully'];
                return back()->withNotify($notify);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Bid placement failed: ' . $e->getMessage(), [
                    'listing_id' => $listingId,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            }, $request, 'bid_placement');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return $this->handleException($e, $request, 'bid_placement');
        }
    }

    public function buyNow(Request $request, $listingId)
    {
        try {
            $user = auth()->user();
            
            // Lock listing to prevent concurrent buy-now purchases
            $listing = Listing::lockForUpdate()
                ->active()
                ->where('buy_now_price', '>', 0)
                ->findOrFail($listingId);

            // Cannot buy own listing
            if ($listing->user_id === $user->id) {
                $notify[] = ['error', 'You cannot buy your own listing'];
                return back()->withNotify($notify);
            }

            // Check if already sold or in escrow
            if ($listing->status === Status::LISTING_SOLD || $listing->escrow_id) {
                $notify[] = ['error', 'This listing is no longer available'];
                return back()->withNotify($notify);
            }

            // Check if buy now price is set
            if (!$listing->buy_now_price || $listing->buy_now_price <= 0) {
                $notify[] = ['error', 'Buy Now option is not available for this listing'];
                return back()->withNotify($notify);
            }

            // Check user balance
            $paymentMode = ($listing->payout_method ?? 'system') === 'direct' ? 'direct' : 'system';
            $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees((float) $listing->buy_now_price, 'escrow_service_fee', $paymentMode);
            $buyerFee = (float) ($feeBreakdown['buyer'] ?? $feeBreakdown['total'] ?? 0);
            $isDirect = ($listing->payout_method ?? 'system') === 'direct';
            $totalNeeded = $isDirect ? $buyerFee : ($listing->buy_now_price + $buyerFee);

            // Allow proceeding without balance – user can pay on the escrow page via wallet or pay via gateway
            if ($user->balance < $totalNeeded) {
                $notify[] = ['info', 'You will be redirected to complete payment. You can pay from your wallet or pay via gateway.'];
            }

            DB::beginTransaction();
            
            try {
                // Create winning bid
                $bid = new Bid();
                $bid->bid_number = getTrx();
                $bid->listing_id = $listing->id;
                $bid->user_id = $user->id;
                $bid->amount = $listing->buy_now_price;
                $bid->status = Status::BID_WON;
                $bid->is_buy_now = true;
                $bid->ip_address = $request->ip();
                $bid->save();

                // Mark other bids as lost
                Bid::where('listing_id', $listing->id)
                    ->where('id', '!=', $bid->id)
                    ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
                    ->update(['status' => Status::BID_LOST]);

                // Update listing - don't mark as SOLD yet, just set escrow_id to hide from public
                // Keep status as LISTING_ACTIVE - it will be hidden from public because escrow_id is set
                $listing->winner_id = $user->id;
                $listing->final_price = $listing->buy_now_price;
                $listing->current_bid = $listing->buy_now_price;
                $listing->highest_bidder_id = $user->id;
                // Don't set sold_at yet - will be set when escrow is completed

                // Create escrow for the transaction
                $escrow = $this->createEscrow($listing, $user, $listing->buy_now_price);
                $listing->escrow_id = $escrow->id;
                $listing->save();

                DB::commit();

                notifySuperAdminsForEscrowAccepted($escrow);

                // Notify seller (outside transaction)
                notify($listing->seller, 'LISTING_SOLD_BUY_NOW', [
                    'listing_title' => $listing->title,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'amount' => showAmount($listing->buy_now_price),
                    'buyer' => $user->username,
                    'buyer_username' => $user->username,
                    'escrow_number' => $escrow->escrow_number ?? '',
                    'escrow_id' => $escrow->id ?? '',
                    'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                ]);

                // Notify buyer
                notify($user, 'PURCHASE_BUY_NOW', [
                    'listing_title' => $listing->title,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'amount' => showAmount($listing->buy_now_price),
                    'seller_username' => $listing->seller->username ?? $listing->seller->name ?? 'Seller',
                    'escrow_number' => $escrow->escrow_number ?? '',
                    'escrow_id' => $escrow->id ?? '',
                    'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                ]);

                $notify[] = ['success', 'Purchase successful! Please proceed to payment.'];
                return redirect()->route('user.escrow.details', $escrow->id)->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Buy Now failed: ' . $e->getMessage(), [
                    'listing_id' => $listingId,
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Buy Now error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while processing your purchase. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $bid = Bid::where('user_id', $user->id)
            ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
            ->with('listing')
            ->findOrFail($id);

        // Check if auction allows bid cancellation
        $listing = $bid->listing;
        if ($listing->auction_end && $listing->auction_end->isFuture()) {
            $hoursRemaining = now()->diffInHours($listing->auction_end, false);
            if ($hoursRemaining > 0 && $hoursRemaining < 24) {
                $notify[] = ['error', 'Cannot cancel bid within 24 hours of auction end'];
                return back()->withNotify($notify);
            }
        }

        $wasWinning = $bid->status === Status::BID_WINNING;

        $bid->status = Status::BID_CANCELLED;
        $bid->save();

        // If this was the winning bid, find new highest
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

        $notify[] = ['success', 'Bid cancelled successfully'];
        return back()->withNotify($notify);
    }

    public function wonAuctions()
    {
        $pageTitle = 'Won Auctions';
        $user = auth()->user();

        $bids = Bid::where('user_id', $user->id)
            ->where('status', Status::BID_WON)
            ->with(['listing.images', 'listing.seller', 'listing.escrow'])
            ->orderBy('updated_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.bid.won', compact('pageTitle', 'bids'));
    }

    private function createEscrow($listing, $buyer, $amount)
    {
        $seller = $listing->seller;
        $paymentMode = ($listing->payout_method ?? 'system') === 'direct' ? 'direct' : 'system';
        $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees((float) $amount, 'escrow_service_fee', $paymentMode);
        $charge = (float) ($feeBreakdown['total'] ?? 0);
        $buyerCharge = (float) ($feeBreakdown['buyer'] ?? $charge);
        $sellerCharge = (float) ($feeBreakdown['seller'] ?? 0);
        $chargePayer = $buyerCharge > 0 && $sellerCharge > 0 ? 3 : ($sellerCharge > 0 ? Status::CHARGE_PAYER_SELLER : Status::CHARGE_PAYER_BUYER);

        $escrow = new Escrow();
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
        $escrow->title = 'Purchase: ' . $listing->title;
        $escrow->details = "Escrow for listing: {$listing->title}\nListing #: {$listing->listing_number}";
        $escrow->status = Status::ESCROW_ACCEPTED;
        $escrow->save();

        // Create conversation for escrow
        $conversation = new Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;
        $conversation->save();

        return $escrow;
    }

    /**
     * Check if auction should be auto-extended on last-minute bid
     */
    private function checkAutoExtend($listing)
    {
        if ($listing->sale_type !== 'auction' || !$listing->auction_end) {
            return;
        }

        try {
            $autoExtendMinutes = MarketplaceSetting::autoExtendAuctionMinutes();
            $bidExtensionThresholdMinutes = MarketplaceSetting::bidExtensionThresholdMinutes();
            
            // If auto-extend is disabled, don't extend
            if ($autoExtendMinutes <= 0) {
                return;
            }
            
            // If threshold is 0, auto-extend is disabled
            if ($bidExtensionThresholdMinutes <= 0) {
                return;
            }

            // Check if auction is ending within the bid extension threshold
            $minutesUntilEnd = $listing->auction_end->diffInMinutes(now(), false);
            
            // If bid is placed within the threshold minutes before auction end, extend it
            if ($minutesUntilEnd > 0 && $minutesUntilEnd <= $bidExtensionThresholdMinutes) {
                // Extend auction by the configured auto-extend minutes
                $oldEndTime = $listing->auction_end->copy();
                $listing->auction_end = $listing->auction_end->addMinutes($autoExtendMinutes);
                $listing->save(); // Save the extended end time
                
                Log::info('Auction auto-extended due to last-minute bid', [
                    'listing_id' => $listing->id,
                    'old_end' => $oldEndTime->toIso8601String(),
                    'new_end' => $listing->auction_end->toIso8601String(),
                    'extended_by_minutes' => $autoExtendMinutes,
                    'threshold_minutes' => $bidExtensionThresholdMinutes,
                    'minutes_until_end' => $minutesUntilEnd
                ]);
            }
        } catch (\Exception $e) {
            // Don't fail bid placement if auto-extend check fails
            Log::warning('Auto-extend check failed: ' . $e->getMessage(), [
                'listing_id' => $listing->id,
                'exception' => $e->getTraceAsString()
            ]);
        }
    }
}

