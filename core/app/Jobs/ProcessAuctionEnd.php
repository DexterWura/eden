<?php

namespace App\Jobs;

use App\Constants\Status;
use App\Models\Bid;
use App\Models\Escrow;
use App\Models\Listing;
use App\Services\EscrowFeeCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAuctionEnd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $listingId;
    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($listingId)
    {
        $this->listingId = $listingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            try {
                // Lock listing to prevent concurrent processing with exclusive lock
                $listing = Listing::lockForUpdate()
                    ->where('id', $this->listingId)
                    ->where('sale_type', 'auction')
                    ->where('status', Status::LISTING_ACTIVE)
                    ->first();

                if (!$listing) {
                    Log::info('Auction already processed or not found', ['listing_id' => $this->listingId]);
                    DB::rollBack();
                    return;
                }

                // Double-check auction has actually ended
                if ($listing->auction_end && $listing->auction_end->isFuture()) {
                    Log::info('Auction has not ended yet', [
                        'listing_id' => $this->listingId,
                        'auction_end' => $listing->auction_end
                    ]);
                    DB::rollBack();
                    return;
                }

                // Check if already processed (double-check after lock)
                if (in_array($listing->status, [Status::LISTING_SOLD, Status::LISTING_EXPIRED, Status::LISTING_CANCELLED])) {
                    Log::info('Auction already processed', ['listing_id' => $this->listingId, 'status' => $listing->status]);
                    DB::rollBack();
                    return;
                }

                // Verify auction actually ended and lock bids for this listing
                $winningBid = Bid::where('listing_id', $listing->id)
                    ->where('status', Status::BID_WINNING)
                    ->with('user')
                    ->lockForUpdate()
                    ->first();

                if (!$winningBid) {
                    $hasAnyBids = Bid::where('listing_id', $listing->id)
                        ->where('status', '!=', Status::BID_CANCELLED)
                        ->exists();

                    if (!$hasAnyBids) {
                        $listing->status = Status::LISTING_EXPIRED;
                        $listing->save();
                        DB::commit();
                        notify($listing->seller, 'AUCTION_ENDED_NO_BIDS', [
                            'listing_title' => $listing->title,
                            'listing_number' => $listing->listing_number,
                            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                        ]);
                        Log::info('Auction ended with no bids', ['listing_id' => $this->listingId]);
                        return;
                    }

                    // Recover winner from highest non-cancelled bid (inconsistent state: no BID_WINNING but bids exist)
                    $winningBid = Bid::where('listing_id', $listing->id)
                        ->where('status', '!=', Status::BID_CANCELLED)
                        ->with('user')
                        ->orderBy('amount', 'desc')
                        ->lockForUpdate()
                        ->first();

                    if (!$winningBid) {
                        $listing->status = Status::LISTING_EXPIRED;
                        $listing->save();
                        DB::commit();
                        Log::warning('Auction recovery: no highest bid found despite hasAnyBids', ['listing_id' => $this->listingId]);
                        return;
                    }

                    Log::info('Auction recovery: using highest bid as winner (no BID_WINNING)', [
                        'listing_id' => $this->listingId,
                        'bid_id' => $winningBid->id,
                    ]);
                }

                // Check if reserve was met (use winning bid amount so recovered winner is evaluated correctly)
                if ($listing->reserve_price > 0 && $winningBid->amount < $listing->reserve_price) {
                    $listing->status = Status::LISTING_EXPIRED;
                    $listing->save();

                    // Mark all bids as lost
                    Bid::where('listing_id', $listing->id)
                        ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING, Status::BID_OUTBID])
                        ->update(['status' => Status::BID_LOST]);

                    DB::commit();

                    notify($listing->seller, 'AUCTION_ENDED_RESERVE_NOT_MET', [
                        'listing_title' => $listing->title,
                        'listing_number' => $listing->listing_number,
                        'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                        'highest_bid' => showAmount($listing->current_bid),
                        'reserve_price' => showAmount($listing->reserve_price),
                    ]);

                    // Notify bidders
                    $bidders = Bid::where('listing_id', $listing->id)
                        ->where('status', Status::BID_LOST)
                        ->with('user')
                        ->get();
                    
                    foreach ($bidders as $bid) {
                        notify($bid->user, 'AUCTION_ENDED_RESERVE_NOT_MET_BIDDER', [
                            'listing_title' => $listing->title,
                            'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                            'your_bid' => showAmount($bid->amount),
                            'reserve_price' => showAmount($listing->reserve_price),
                        ]);
                    }

                    Log::info('Auction ended - reserve not met', [
                        'listing_id' => $this->listingId,
                        'highest_bid' => $listing->current_bid,
                        'reserve_price' => $listing->reserve_price
                    ]);
                    return;
                }

                // Validate winning bid data integrity
                if (!$winningBid->user || $winningBid->user->status !== Status::USER_ACTIVE) {
                    Log::error('Invalid winning bidder', [
                        'listing_id' => $listing->id,
                        'bid_id' => $winningBid->id,
                        'user_id' => $winningBid->user_id,
                        'user_status' => $winningBid->user?->status
                    ]);

                    // Mark auction as expired instead of processing invalid winner
                    $listing->status = Status::LISTING_EXPIRED;
                    $listing->save();

                    // Mark all bids as lost
                    Bid::where('listing_id', $listing->id)
                        ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING, Status::BID_OUTBID])
                        ->update(['status' => Status::BID_LOST]);

                    DB::commit();

                    notify($listing->seller, 'AUCTION_ENDED_INVALID_WINNER', [
                        'listing_title' => $listing->title,
                        'listing_number' => $listing->listing_number,
                        'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    ]);

                    return;
                }

                // Winner found - process sale
                $winningBid->status = Status::BID_WON;
                $winningBid->save();

                // Mark other bids as lost
                Bid::where('listing_id', $listing->id)
                    ->where('id', '!=', $winningBid->id)
                    ->whereIn('status', [Status::BID_ACTIVE, Status::BID_OUTBID])
                    ->update(['status' => Status::BID_LOST]);

                // Update listing - set escrow_id to hide from public
                $listing->winner_id = $winningBid->user_id;
                $listing->final_price = $winningBid->amount;
                // Don't set sold_at yet - will be set when escrow is completed
                // Keep status as LISTING_ACTIVE - it will be hidden from public because escrow_id is set

                // Create escrow with validation
                try {
                    $escrow = $this->createEscrow($listing, $winningBid->user, $winningBid->amount);
                    $listing->escrow_id = $escrow->id;
                    $listing->save();
                } catch (\Exception $e) {
                    Log::error('Failed to create escrow for auction', [
                        'listing_id' => $listing->id,
                        'bid_id' => $winningBid->id,
                        'error' => $e->getMessage()
                    ]);

                    // Rollback bid changes and mark auction as expired
                    $winningBid->status = Status::BID_LOST;
                    $winningBid->save();

                    $listing->status = Status::LISTING_EXPIRED;
                    $listing->save();

                    DB::commit();

                    notify($listing->seller, 'AUCTION_PROCESSING_ERROR', [
                        'listing_title' => $listing->title,
                        'listing_number' => $listing->listing_number,
                        'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    ]);

                    return;
                }

               
                // Users can create milestones manually if they want, or pay the full amount

                // Don't update user stats yet - will be updated when escrow is completed

                DB::commit();

                notifySuperAdminsForEscrowAccepted($escrow);

                // Notify winner (outside transaction) - both email and database notification
                notify($winningBid->user, 'AUCTION_WON', [
                    'listing_title' => $listing->title,
                    'listing_number' => $listing->listing_number,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'winning_bid' => showAmount($winningBid->amount),
                    'escrow_number' => $escrow->escrow_number,
                    'escrow_id' => $escrow->id,
                    'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                    'seller_username' => $listing->seller->username ?? $listing->seller->name ?? 'Seller',
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
                notify($listing->seller, 'AUCTION_ENDED_SOLD', [
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
                    $winningBid->user->username ?? $winningBid->user->name,
                    $escrow->escrow_number,
                    $escrow->id
                ));

                // Notify outbid bidders
                $outbidBidders = Bid::where('listing_id', $listing->id)
                    ->where('status', Status::BID_LOST)
                    ->where('id', '!=', $winningBid->id)
                    ->with('user')
                    ->get();
                
                foreach ($outbidBidders as $bid) {
                    notify($bid->user, 'AUCTION_ENDED_OUTBID', [
                        'listing_title' => $listing->title,
                        'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                        'winning_bid' => showAmount($winningBid->amount),
                        'your_bid' => showAmount($bid->amount),
                        'winner_username' => $winningBid->user->username ?? $winningBid->user->name ?? 'Winner',
                    ]);
                }

                Log::info('Auction processed successfully', [
                    'listing_id' => $this->listingId,
                    'winner_id' => $winningBid->user_id,
                    'final_price' => $winningBid->amount,
                    'escrow_id' => $escrow->id
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auction processing failed: ' . $e->getMessage(), [
                    'listing_id' => $this->listingId,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Auction processing error: ' . $e->getMessage(), [
                'listing_id' => $this->listingId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create escrow for auction sale
     */
    private function createEscrow($listing, $buyer, $amount)
    {
        // Validate required data
        if (!$listing->seller) {
            throw new \Exception('Listing seller not found');
        }

        if (!$buyer || $buyer->status !== Status::USER_ACTIVE) {
            throw new \Exception('Invalid or inactive buyer');
        }

        if ($amount <= 0) {
            throw new \Exception('Invalid escrow amount');
        }

        $seller = $listing->seller;
        $paymentMode = ($listing->payout_method ?? 'system') === 'direct' ? 'direct' : 'system';
        $feeBreakdown = app(EscrowFeeCalculator::class)->calculateMarketplaceFees((float) $amount, 'escrow_service_fee', $paymentMode);
        $charge = (float) ($feeBreakdown['total'] ?? 0);
        $buyerCharge = (float) ($feeBreakdown['buyer'] ?? $charge);
        $sellerCharge = (float) ($feeBreakdown['seller'] ?? 0);
        $chargePayer = $buyerCharge > 0 && $sellerCharge > 0 ? 3 : ($sellerCharge > 0 ? Status::CHARGE_PAYER_SELLER : Status::CHARGE_PAYER_BUYER);

        // Ensure buyer has sufficient balance for charges
        if ($buyer->balance < $buyerCharge) {
            throw new \Exception('Buyer has insufficient balance for escrow charges');
        }

        $escrow = new Escrow();
        $escrow->escrow_number = getTrx();
        $escrow->seller_id = $seller->id;
        $escrow->buyer_id = $buyer->id;
        $escrow->creator_id = $buyer->id;
        if (($listing->payout_method ?? 'system') === 'direct') {
            $escrow->payment_mode = 'direct';
            $escrow->external_amount = $amount;
            $escrow->external_payment_link = $listing->direct_payment_link;
            $escrow->amount = 0;
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

        if (!$escrow->save()) {
            throw new \Exception('Failed to save escrow record');
        }

        // Create conversation
        $conversation = new \App\Models\Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;

        if (!$conversation->save()) {
            // If conversation fails, delete escrow and rethrow
            $escrow->delete();
            throw new \Exception('Failed to create escrow conversation');
        }

        return $escrow;
    }

}

