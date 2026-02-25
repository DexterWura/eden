<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Offer;
use App\Services\EscrowFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Offers';
        $user = auth()->user();

        // Auto-expire old offers
        $this->expireOldOffers();

        $offers = Offer::where('buyer_id', $user->id)
            ->with(['listing.images', 'seller'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->search, function ($q, $search) {
                return $q->whereHas('listing', function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.offer.index', compact('pageTitle', 'offers'));
    }

    public function received(Request $request)
    {
        $pageTitle = 'Received Offers';
        $user = auth()->user();

        // Auto-expire old offers
        $this->expireOldOffers();

        $offers = Offer::where('seller_id', $user->id)
            ->with(['listing.images', 'buyer'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->search, function ($q, $search) {
                return $q->whereHas('listing', function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.offer.received', compact('pageTitle', 'offers'));
    }

    public function make(Request $request, $listingId)
    {
        $user = auth()->user();
        $listing = Listing::active()
            ->fixedPrice()
            ->with('seller')
            ->findOrFail($listingId);

        // Cannot make offer on own listing
        if ($listing->user_id === $user->id) {
            $notify[] = ['error', 'You cannot make an offer on your own listing'];
            return back()->withNotify($notify);
        }

        // Check if user account is fully verified
        if (!$user->ev || !$user->sv) {
            $notify[] = ['error', 'Please verify your email and mobile number before making offers'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:1000',
        ]);

        // Validate offer amount is reasonable
        if ($listing->asking_price > 0) {
            // Offer should be at least 10% of asking price (common sense)
            $minimumOffer = $listing->asking_price * 0.1;
            if ($request->amount < $minimumOffer) {
                $notify[] = ['error', 'Offer amount is too low. Minimum offer should be at least ' . showAmount($minimumOffer) . ' (10% of asking price)'];
                return back()->withInput()->withNotify($notify);
            }

            // Warn if offer is significantly higher than asking price
            if ($request->amount > $listing->asking_price * 1.1) {
                $notify[] = ['warning', 'Your offer is higher than the asking price. Consider using Buy Now instead.'];
                // Don't block, just warn
            }
        }

        // Check for pending offer from same user
        $existingOffer = Offer::where('listing_id', $listing->id)
            ->where('buyer_id', $user->id)
            ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->first();

        if ($existingOffer) {
            $notify[] = ['error', 'You already have a pending offer on this listing'];
            return back()->withNotify($notify);
        }

        // Check if listing has an existing escrow (even if unfunded)
        // This prevents confusion where offers are made but can't be accepted
        if ($listing->escrow_id) {
            $escrow = $listing->escrow;
            if ($escrow && $escrow->status !== Status::ESCROW_CANCELLED) {
                $notify[] = ['error', 'This listing already has an offer being processed. Please wait for that transaction to complete or be cancelled.'];
                return back()->withNotify($notify);
            }
        }

        // Check user balance (rough estimate for potential acceptance)
        $general = gs();
        $charge = ($request->amount * ($general->percent_charge ?? 0) / 100) + ($general->fixed_charge ?? 0);
        if ($charge > ($general->charge_cap ?? 0) && $general->charge_cap > 0) {
            $charge = $general->charge_cap;
        }
        $totalNeeded = $request->amount + $charge;

        // If user balance is low, we intentionally do NOT show "top up/deposit" warnings here.
        // Offer creation should not nag users about wallet funding at this stage.
        // if ($user->balance < $totalNeeded) {
        //     $notify[] = ['warning', 'You may need to deposit funds if your offer is accepted. Required: ' . showAmount($totalNeeded) . ' (including fees). Current balance: ' . showAmount($user->balance)];
        // }

        $offer = new Offer();
        $offer->offer_number = getTrx();
        $offer->listing_id = $listing->id;
        $offer->buyer_id = $user->id;
        $offer->seller_id = $listing->user_id;
        $offer->amount = $request->amount;
        $offer->message = $request->message;
        $offer->status = Status::OFFER_PENDING;
        $offer->expires_at = now()->addDays(7);
        $offer->save();

        // Log offer creation
        \Log::info('Offer created', [
            'offer_id' => $offer->id,
            'offer_number' => $offer->offer_number,
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $listing->user_id,
            'amount' => $request->amount,
            'asking_price' => $listing->asking_price,
            'expires_at' => $offer->expires_at,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String()
        ]);

        // Notify seller
        notify($listing->seller, 'NEW_OFFER_RECEIVED', [
            'listing_title' => $listing->title,
            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
            'offer_amount' => showAmount($request->amount),
            'asking_price' => showAmount($listing->asking_price),
            'buyer' => $user->username,
            'buyer_username' => $user->username,
            'message' => $request->message ?? 'No message',
        ]);
        
        // Send database notification
        $listing->seller->notify(new \App\Notifications\NewOfferReceived($offer, $listing));

        $notify[] = ['success', 'Offer submitted successfully'];
        return back()->withNotify($notify);
    }

    public function accept($id)
    {
        try {
            $user = auth()->user();
            
            // Lock offer and listing to prevent concurrent acceptances
            $offer = Offer::lockForUpdate()
                ->where('seller_id', $user->id)
                ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
                ->with(['listing' => function($q) {
                    $q->lockForUpdate();
                }, 'buyer'])
                ->findOrFail($id);

            $listing = $offer->listing;

            // Check if offer has expired
            if ($offer->expires_at && $offer->expires_at < now()) {
                $offer->status = Status::OFFER_EXPIRED;
                $offer->save();
                $notify[] = ['error', 'This offer has expired'];
                return back()->withNotify($notify);
            }

            // Check if listing is already in escrow or sold
            if ($listing->status === Status::LISTING_SOLD || $listing->escrow_id) {
                $notify[] = ['error', 'This listing is no longer available'];
                return back()->withNotify($notify);
            }

            // Check buyer balance
            $finalAmount = $offer->counter_amount > 0 ? $offer->counter_amount : $offer->amount;
            $paymentMode = ($listing->payout_method ?? 'system') === 'direct' ? 'direct' : 'system';
            $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees((float) $finalAmount, 'escrow_service_fee', $paymentMode);
            $buyerFee = (float) ($feeBreakdown['buyer'] ?? $feeBreakdown['total'] ?? 0);
            $totalNeeded = ($listing->payout_method ?? 'system') === 'direct' ? $buyerFee : ($finalAmount + $buyerFee);

            // Buyer can pay on the escrow page via wallet or pay via gateway – no need to block here
            if ($offer->buyer->balance < $totalNeeded) {
                $notify[] = ['info', 'Buyer will complete payment on the escrow page (wallet or pay via gateway).'];
            }

            DB::beginTransaction();
            
            try {
                // Reject other pending offers
                Offer::where('listing_id', $listing->id)
                    ->where('id', '!=', $offer->id)
                    ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
                    ->update([
                        'status' => Status::OFFER_REJECTED,
                        'rejection_reason' => 'Another offer was accepted',
                        'responded_at' => now(),
                    ]);

                // Accept this offer
                $offer->status = Status::OFFER_ACCEPTED;
                $offer->responded_at = now();

                // Update listing - don't mark as SOLD yet, just set escrow_id to hide from public
                $finalAmount = $offer->counter_amount > 0 ? $offer->counter_amount : $offer->amount;
                // Keep status as LISTING_ACTIVE - it will be hidden from public because escrow_id is set
                $listing->winner_id = $offer->buyer_id;
                $listing->final_price = $finalAmount;
                // Don't set sold_at yet - will be set when escrow is completed

                // Create escrow
                $escrow = $this->createEscrow($listing, $offer->buyer, $finalAmount);
                $listing->escrow_id = $escrow->id;
                $listing->save();

                $offer->escrow_id = $escrow->id;
                $offer->save();

                // Don't update user stats yet - will be updated when escrow is completed

                DB::commit();

                notifySuperAdminsForEscrowAccepted($escrow);

                // Notify buyer (outside transaction)
                notify($offer->buyer, 'OFFER_ACCEPTED', [
                    'listing_title' => $listing->title,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'offer_amount' => showAmount($finalAmount),
                    'seller_username' => $listing->seller->username ?? $listing->seller->name ?? 'Seller',
                    'escrow_number' => $escrow->escrow_number ?? '',
                    'escrow_id' => $escrow->id ?? '',
                    'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                ]);

                $notify[] = ['success', 'Offer accepted. Escrow has been created.'];
                return redirect()->route('user.escrow.details', $escrow->id)->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Offer acceptance failed: ' . $e->getMessage(), [
                    'offer_id' => $id,
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Offer acceptance error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while accepting the offer. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        $offer = Offer::where('seller_id', $user->id)
            ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->with(['listing', 'buyer'])
            ->findOrFail($id);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $offer->status = Status::OFFER_REJECTED;
        $offer->rejection_reason = $request->reason;
        $offer->responded_at = now();
        $offer->save();

        // Notify buyer
        notify($offer->buyer, 'OFFER_REJECTED', [
            'listing_title' => $offer->listing->title,
            'listing_url' => url(route('marketplace.listing.show', $offer->listing->slug)),
            'offer_amount' => showAmount($offer->amount),
            'reason' => $request->reason ?? 'No reason provided',
            'seller_username' => $offer->seller->username ?? $offer->seller->name ?? 'Seller',
        ]);

        $notify[] = ['success', 'Offer rejected'];
        return back()->withNotify($notify);
    }

    public function counter(Request $request, $id)
    {
        $user = auth()->user();
        $offer = Offer::where('seller_id', $user->id)
            ->where('status', Status::OFFER_PENDING)
            ->with(['listing', 'buyer'])
            ->findOrFail($id);

        $request->validate([
            'counter_amount' => 'required|numeric|min:1',
            'counter_message' => 'nullable|string|max:1000',
        ]);

        // Validate counter offer is reasonable
        if ($request->counter_amount < $offer->amount) {
            $notify[] = ['error', 'Counter offer must be higher than the original offer'];
            return back()->withInput()->withNotify($notify);
        }

        if ($offer->listing->asking_price > 0 && $request->counter_amount > $offer->listing->asking_price * 1.2) {
            $notify[] = ['warning', 'Counter offer is significantly higher than asking price'];
            // Don't block, just warn
        }

        $offer->status = Status::OFFER_COUNTERED;
        $offer->counter_amount = $request->counter_amount;
        $offer->counter_message = $request->counter_message;
        $offer->countered_at = now();
        $offer->expires_at = now()->addDays(7);
        $offer->save();

        // Notify buyer
        notify($offer->buyer, 'OFFER_COUNTERED', [
            'listing_title' => $offer->listing->title,
            'listing_url' => url(route('marketplace.listing.show', $offer->listing->slug)),
            'original_amount' => showAmount($offer->amount),
            'counter_amount' => showAmount($request->counter_amount),
            'message' => $request->counter_message ?? 'No message',
            'seller_username' => $offer->seller->username ?? $offer->seller->name ?? 'Seller',
        ]);
        
        // Send database notification
        $offer->buyer->notify(new \App\Notifications\CounterOfferReceived($offer, $offer->listing));

        $notify[] = ['success', 'Counter offer sent'];
        return back()->withNotify($notify);
    }

    public function acceptCounter($id)
    {
        try {
            $user = auth()->user();
            
            // Lock offer and listing to prevent concurrent acceptances
            $offer = Offer::lockForUpdate()
                ->where('buyer_id', $user->id)
                ->where('status', Status::OFFER_COUNTERED)
                ->with(['listing' => function($q) {
                    $q->lockForUpdate();
                }, 'seller'])
                ->findOrFail($id);

            $listing = $offer->listing;
            $finalAmount = $offer->counter_amount;

            // Check if offer has expired
            if ($offer->expires_at && $offer->expires_at < now()) {
                $notify[] = ['error', 'This offer has expired'];
                return back()->withNotify($notify);
            }

            // Check if listing is still available
            if ($listing->status === Status::LISTING_SOLD || $listing->escrow_id) {
                $notify[] = ['error', 'This listing is no longer available'];
                return back()->withNotify($notify);
            }

            // Check user balance
            $paymentMode = ($listing->payout_method ?? 'system') === 'direct' ? 'direct' : 'system';
            $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees((float) $finalAmount, 'escrow_service_fee', $paymentMode);
            $buyerFee = (float) ($feeBreakdown['buyer'] ?? $feeBreakdown['total'] ?? 0);
            $totalNeeded = ($listing->payout_method ?? 'system') === 'direct' ? $buyerFee : ($finalAmount + $buyerFee);

            // You can pay on the escrow page via wallet or pay via gateway – no need to block here
            if ($user->balance < $totalNeeded) {
                $notify[] = ['info', 'You will complete payment on the escrow page (wallet or pay via gateway).'];
            }

            DB::beginTransaction();
            
            try {

                // Accept counter offer
                $offer->status = Status::OFFER_ACCEPTED;
                $offer->responded_at = now();

                // Update listing - don't mark as SOLD yet, just set escrow_id to hide from public
                // Keep status as LISTING_ACTIVE - it will be hidden from public because escrow_id is set
                $listing->winner_id = $user->id;
                $listing->final_price = $finalAmount;
                // Don't set sold_at yet - will be set when escrow is completed

                // Reject other offers
                Offer::where('listing_id', $listing->id)
                    ->where('id', '!=', $offer->id)
                    ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
                    ->update([
                        'status' => Status::OFFER_REJECTED,
                        'rejection_reason' => 'Another offer was accepted',
                        'responded_at' => now(),
                    ]);

                // Create escrow
                $escrow = $this->createEscrow($listing, $user, $finalAmount);
                $listing->escrow_id = $escrow->id;
                $listing->save();

                $offer->escrow_id = $escrow->id;
                $offer->save();

                DB::commit();

                notifySuperAdminsForEscrowAccepted($escrow);

                // Refresh offer to get latest data including escrow_id
                $offer->refresh();

                // Notify seller (outside transaction)
                notify($offer->seller, 'COUNTER_OFFER_ACCEPTED', [
                    'listing_title' => $listing->title,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'amount' => showAmount($finalAmount),
                    'buyer' => $user->username,
                    'buyer_username' => $user->username,
                    'escrow_number' => $escrow->escrow_number ?? '',
                    'escrow_id' => $escrow->id ?? '',
                    'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                ]);
                
                // Send database notification to seller
                $offer->seller->notify(new \App\Notifications\CounterOfferAccepted($offer, $listing));

                $notify[] = ['success', 'Counter offer accepted. Escrow has been created.'];
                return redirect()->route('user.escrow.details', $escrow->id)->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Counter offer acceptance failed: ' . $e->getMessage(), [
                    'offer_id' => $id,
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Counter offer acceptance error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while accepting the counter offer. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $offer = Offer::where('buyer_id', $user->id)
            ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->findOrFail($id);

        $offer->status = Status::OFFER_CANCELLED;
        $offer->save();

        $notify[] = ['success', 'Offer cancelled'];
        return back()->withNotify($notify);
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

        // Create conversation
        $conversation = new Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;
        $conversation->save();

        return $escrow;
    }

    /**
     * Expire old offers that have passed their expiration date
     */
    private function expireOldOffers()
    {
        $expiredCount = Offer::whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->where('expires_at', '<', now())
            ->update([
                'status' => Status::OFFER_EXPIRED,
                'responded_at' => now(),
            ]);

        if ($expiredCount > 0) {
            Log::info("Expired {$expiredCount} offers");
        }
    }
}

