<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Milestone;
use App\Models\MilestoneTemplate;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MilestoneController extends Controller
{

    public function milestones($id)
    {
        $pageTitle   = "Payment Milestones";
        $escrow      = Escrow::checkUser()->findOrFail($id);

        // Direct-payment escrows do not support milestones (platform does not hold the sale amount).
        if (($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0) {
            $notify[] = ['error', 'Milestones are not available for direct-payment escrows. Please use "Pay Platform Fee" on the escrow page.'];
            return redirect()->route('user.escrow.details', $escrow->id)->withNotify($notify);
        }
        
        // Get milestones ordered by id (sort_order may not exist in all databases)
        $milestones  = Milestone::where('escrow_id', $id)
            ->orderBy('id')
            ->with('deposit:milestone_id,status')
            ->get();
            
        $totalAmount = $milestones->sum('amount');
        $restAmount  = $escrow->amount + $escrow->buyer_charge - $totalAmount;
        
        // Get available templates if no milestones exist yet
        $templates = null;
        if ($milestones->isEmpty() && $escrow->status == Status::ESCROW_ACCEPTED) {
            // Check if milestone_templates table exists before querying
            if (\Illuminate\Support\Facades\Schema::hasTable('milestone_templates')) {
                // Try to get business type from associated listing
                $listing = \App\Models\Listing::where('escrow_id', $escrow->id)->first();
                if ($listing) {
                    try {
                        $templates = MilestoneTemplate::getTemplatesForBusinessType($listing->business_type);
                    } catch (\Exception $e) {
                        // Table or column doesn't exist, skip templates
                        $templates = null;
                    }
                }
            }
        }
        
        return view('Template::user.escrow.milestones', compact('pageTitle', 'escrow', 'milestones', 'restAmount', 'templates'));
    }

    /**
     * Create milestone - Seller can create, Buyer can also create but needs seller approval
     */
    public function createMilestone(Request $request, $id)
    {
        $escrow = Escrow::checkUser()->accepted()->findOrFail($id);
        $user = auth()->user();

        if (($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0) {
            $notify[] = ['error', 'Milestones are not available for direct-payment escrows. Please pay the escrow service fee instead.'];
            return back()->withNotify($notify);
        }
        
        $isSeller = $escrow->seller_id == $user->id;
        $isBuyer = $escrow->buyer_id == $user->id;
        
        if (!$isSeller && !$isBuyer) {
            abort(403, 'Unauthorized');
        }

        // Check if approval_status column exists
        $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
        $milestonesQuery = $escrow->milestones();
        if ($hasApprovalStatus) {
            $milestonesQuery->where('approval_status', '!=', 'rejected');
        }
        $totalAmount = $milestonesQuery->sum('amount');
        $restAmount  = $escrow->amount + $escrow->buyer_charge - $totalAmount;

        $request->validate([
            'amount' => 'required|numeric|gt:0|lte:' . $restAmount,
            'note'   => 'required|string|min:10|max:500',
            'milestone_type' => 'nullable|string|max:50',
        ], [
            'amount.required' => 'Milestone amount is required',
            'amount.gt' => 'Milestone amount must be greater than 0',
            'amount.lte' => 'Milestone amount cannot exceed remaining escrow amount of ' . showAmount($restAmount),
            'note.required' => 'Milestone description is required',
            'note.min' => 'Milestone description must be at least 10 characters',
            'note.max' => 'Milestone description cannot exceed 500 characters',
        ]);

        // Validate amount is reasonable (at least 1% of escrow amount)
        $minAmount = ($escrow->amount + $escrow->buyer_charge) * 0.01;
        if ($request->amount < $minAmount) {
            $notify[] = ['error', 'Milestone amount is too small. Minimum amount is ' . showAmount($minAmount) . ' (1% of escrow amount)'];
            return back()->withInput()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            // Get current max sort_order (if column exists, otherwise use count)
            $maxSortOrder = 0;
            $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
            $sortQuery = Milestone::where('escrow_id', $escrow->id);
            if ($hasApprovalStatus) {
                $sortQuery->where('approval_status', '!=', 'rejected');
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('milestones', 'sort_order')) {
                $maxSortOrder = $sortQuery->max('sort_order') ?? 0;
            } else {
                $maxSortOrder = $sortQuery->count();
            }

            $milestone = new Milestone();
            $milestone->escrow_id = $escrow->id;
            $milestone->user_id = $user->id;
            $milestone->requested_by = $isSeller ? 'seller' : 'buyer';
            $milestone->amount = $request->amount;
            $milestone->note = $request->note;
            $milestone->milestone_type = $request->milestone_type;
            
            // Set sort_order only if column exists (check using Schema)
            if (\Illuminate\Support\Facades\Schema::hasColumn('milestones', 'sort_order')) {
                $milestone->sort_order = $maxSortOrder + 1;
            }
            
            // Auto-approve by the creator
            if ($isSeller) {
                $milestone->approved_by_seller = true;
                $milestone->approved_by_buyer = false;
            } else {
                $milestone->approved_by_seller = false;
                $milestone->approved_by_buyer = true;
            }
            
            // Status is approved only if both parties have approved (only if column exists)
            if ($hasApprovalStatus) {
                $milestone->approval_status = ($milestone->approved_by_seller && $milestone->approved_by_buyer) 
                    ? 'approved' 
                    : 'pending';
            }
            
            $milestone->payment_status = Status::MILESTONE_UNFUNDED;
            $milestone->status = 1;
            $milestone->save();

            DB::commit();

            // Notify the other party
            $otherParty = $isSeller ? $escrow->buyer : $escrow->seller;
            $isPending = $hasApprovalStatus && $milestone->approval_status === 'pending';
            
            // Send email notification (legacy)
            notify($otherParty, 'MILESTONE_CREATED', [
                'escrow_number' => $escrow->escrow_number,
                'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                'milestone_note' => $milestone->note,
                'milestone_amount' => showAmount($milestone->amount),
                'action_required' => $isPending ? 'Please review and approve this milestone' : null,
                'created_by' => $user->username ?? $user->name,
            ]);

            // Send database notification for top bar if milestone needs approval
            if ($isPending) {
                // Count all pending milestones for this escrow
                $pendingMilestones = $escrow->milestones()->where('approval_status', 'pending')->count();
                $listingTitle = $escrow->listing ? $escrow->listing->title : null;
                
                $otherParty->notify(new \App\Notifications\MilestonesPendingApproval(
                    $escrow,
                    $pendingMilestones,
                    $listingTitle
                ));
            }

            $notify[] = ['success', 'Milestone created successfully. ' . ($isPending ? 'Waiting for ' . ($isSeller ? 'buyer' : 'seller') . ' approval.' : 'Milestone is approved and ready.')];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone creation failed: ' . $e->getMessage(), [
                'escrow_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            $notify[] = ['error', 'Failed to create milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Generate milestones from template
     */
    public function generateFromTemplate(Request $request, $id)
    {
        $escrow = Escrow::checkUser()->accepted()->findOrFail($id);
        $user = auth()->user();

        if (($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0) {
            $notify[] = ['error', 'Milestones are not available for direct-payment escrows. Please pay the escrow service fee instead.'];
            return back()->withNotify($notify);
        }
        
        // Only seller can generate from template
        if ($escrow->seller_id != $user->id) {
            abort(403, 'Only seller can generate milestones from template');
        }

        // Check if milestone_templates table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable('milestone_templates')) {
            $notify[] = ['error', 'Milestone templates feature is not available'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'template_id' => 'required|exists:milestone_templates,id',
        ]);

        $template = MilestoneTemplate::findOrFail($request->template_id);
        
        // Check if milestones already exist
        $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
        $milestonesQuery = $escrow->milestones();
        if ($hasApprovalStatus) {
            $milestonesQuery->where('approval_status', '!=', 'rejected');
        }
        if ($milestonesQuery->exists()) {
            $notify[] = ['error', 'Milestones already exist. Please delete existing milestones first.'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            $totalAmount = $escrow->amount + $escrow->buyer_charge;
            $milestones = $template->generateMilestones($escrow, $totalAmount);
            
            Milestone::insert($milestones);

            DB::commit();

            // Notify buyer (email notification)
            notify($escrow->buyer, 'MILESTONES_GENERATED', [
                'escrow_number' => $escrow->escrow_number,
                'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                'template_name' => $template->name,
                'milestone_count' => count($milestones),
                'action_required' => 'Please review and approve the proposed milestones',
                'created_by' => $user->username ?? $user->name,
            ]);

            // Send database notification for top bar if milestones need approval
            $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
            if ($hasApprovalStatus) {
                // Reload milestones to check approval status
                $escrow->refresh();
                $pendingMilestones = $escrow->milestones()->where('approval_status', 'pending')->count();
                if ($pendingMilestones > 0) {
                    $listingTitle = $escrow->listing ? $escrow->listing->title : null;
                    $escrow->buyer->notify(new \App\Notifications\MilestonesPendingApproval(
                        $escrow,
                        $pendingMilestones,
                        $listingTitle
                    ));
                }
            }

            $notify[] = ['success', 'Milestones generated from template. Buyer has been notified to review.'];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone generation failed: ' . $e->getMessage(), [
                'escrow_id' => $id,
                'template_id' => $request->template_id,
                'trace' => $e->getTraceAsString()
            ]);
            $notify[] = ['error', 'Failed to generate milestones. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Approve milestone
     */
    public function approveMilestone($id)
    {
        $milestone = Milestone::whereHas('escrow', function ($query) {
            $query->checkUser();
        })->findOrFail($id);
        
        $escrow = $milestone->escrow;
        $user = auth()->user();

        if (($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0) {
            $notify[] = ['error', 'Milestones are not available for direct-payment escrows.'];
            return back()->withNotify($notify);
        }
        
        $isSeller = $escrow->seller_id == $user->id;
        $isBuyer = $escrow->buyer_id == $user->id;
        
        if (!$isSeller && !$isBuyer) {
            abort(403, 'Unauthorized');
        }

        // Can't approve own request if you're the only one who needs to approve
        if (($isSeller && $milestone->requested_by === 'seller' && $milestone->approved_by_seller) ||
            ($isBuyer && $milestone->requested_by === 'buyer' && $milestone->approved_by_buyer)) {
            $notify[] = ['error', 'You have already approved this milestone'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            if ($isSeller) {
                $milestone->approved_by_seller = true;
            } else {
                $milestone->approved_by_buyer = true;
            }
            
            // Check if both parties have approved
            $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
            if ($milestone->approved_by_seller && $milestone->approved_by_buyer) {
                if ($hasApprovalStatus) {
                    $milestone->approval_status = 'approved';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approved_at')) {
                    $milestone->approved_at = now();
                }
            }
            
            $milestone->save();

            DB::commit();

            // Notify the other party
            $otherParty = $isSeller ? $escrow->buyer : $escrow->seller;
            notify($otherParty, 'MILESTONE_APPROVED', [
                'escrow_number' => $escrow->escrow_number,
                'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                'milestone_note' => $milestone->note,
                'milestone_amount' => showAmount($milestone->amount),
                'approved_by' => $user->username ?? $user->name,
                'approved_by_username' => $user->username ?? $user->name,
            ]);

            $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
            $isApproved = $hasApprovalStatus && $milestone->approval_status === 'approved';
            $message = $isApproved 
                ? 'Milestone approved by both parties and is now active.'
                : 'Your approval has been recorded. Waiting for ' . ($isSeller ? 'buyer' : 'seller') . ' approval.';
            
            $notify[] = ['success', $message];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone approval failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to approve milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Reject milestone
     */
    public function rejectMilestone(Request $request, $id)
    {
        $milestone = Milestone::whereHas('escrow', function ($query) {
            $query->checkUser();
        })->findOrFail($id);
        
        $escrow = $milestone->escrow;
        $user = auth()->user();

        if (($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0) {
            $notify[] = ['error', 'Milestones are not available for direct-payment escrows.'];
            return back()->withNotify($notify);
        }
        
        $isSeller = $escrow->seller_id == $user->id;
        $isBuyer = $escrow->buyer_id == $user->id;
        
        if (!$isSeller && !$isBuyer) {
            abort(403, 'Unauthorized');
        }

        // Can't reject if already approved by both parties
        $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
        $isApproved = $hasApprovalStatus ? ($milestone->approval_status === 'approved') : ($milestone->approved_by_seller && $milestone->approved_by_buyer);
        if ($isApproved) {
            $notify[] = ['error', 'Cannot reject an approved milestone'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
        $isApproved = $hasApprovalStatus ? ($milestone->approval_status === 'approved') : ($milestone->approved_by_seller && $milestone->approved_by_buyer);
        if ($isApproved) {
            $notify[] = ['error', 'Cannot reject an approved milestone'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            if ($hasApprovalStatus) {
                $milestone->approval_status = 'rejected';
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('milestones', 'rejection_reason')) {
                $milestone->rejection_reason = $request->rejection_reason;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('milestones', 'rejected_by')) {
                $milestone->rejected_by = $user->id;
            }
            $milestone->save();

            DB::commit();

            // Notify the other party
            $otherParty = $isSeller ? $escrow->buyer : $escrow->seller;
            notify($otherParty, 'MILESTONE_REJECTED', [
                'escrow_number' => $escrow->escrow_number,
                'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                'milestone_note' => $milestone->note,
                'milestone_amount' => showAmount($milestone->amount),
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => $user->username ?? $user->name,
                'rejected_by_username' => $user->username ?? $user->name,
            ]);

            $notify[] = ['success', 'Milestone rejected. The other party has been notified.'];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone rejection failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to reject milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Delete milestone (only if not approved or if creator)
     */
    public function deleteMilestone($id)
    {
        $milestone = Milestone::whereHas('escrow', function ($query) {
            $query->checkUser();
        })->findOrFail($id);
        
        $escrow = $milestone->escrow;
        $user = auth()->user();

        if (($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0) {
            $notify[] = ['error', 'Milestones are not available for direct-payment escrows.'];
            return back()->withNotify($notify);
        }
        
        // Can only delete if:
        // 1. Not approved by both parties, OR
        // 2. You created it and it's not funded
        $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
        $isApproved = $hasApprovalStatus ? ($milestone->approval_status === 'approved') : ($milestone->approved_by_seller && $milestone->approved_by_buyer);
        if ($isApproved && $milestone->payment_status === Status::MILESTONE_FUNDED) {
            $notify[] = ['error', 'Cannot delete a funded milestone'];
            return back()->withNotify($notify);
        }
        
        if ($milestone->user_id != $user->id && $isApproved) {
            $notify[] = ['error', 'Cannot delete an approved milestone created by someone else'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            $milestone->delete();

            DB::commit();

            $notify[] = ['success', 'Milestone deleted successfully'];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone deletion failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to delete milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function payMilestone(Request $request, $id)
    {
        try {
            $request->validate([
                'pay_via' => 'required|in:1,2',
            ]);

            $milestone = Milestone::unFunded()->whereHas('escrow', function ($query) {
                $query->where('buyer_id', auth()->user()->id);
            })->with('deposit:milestone_id,status')->find($id);

            if (!$milestone) {
                $notify[] = ['error', 'Milestone not found'];
                return back()->withNotify($notify);
            }

            // Check if milestone is approved by both parties (if approval_status column exists)
            $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
            if ($hasApprovalStatus && $milestone->approval_status !== 'approved') {
                $notify[] = ['error', 'You can only pay for milestones that are approved by both parties'];
                return back()->withNotify($notify);
            }
            // If approval_status doesn't exist, check if both parties have approved using boolean fields
            if (!$hasApprovalStatus && (!($milestone->approved_by_seller && $milestone->approved_by_buyer))) {
                $notify[] = ['error', 'You can only pay for milestones that are approved by both parties'];
                return back()->withNotify($notify);
            }

            if ($milestone->deposit && $milestone->deposit->status == Status::PAYMENT_PENDING) {
                $notify[] = ['error', 'Payment for this milestone is pending now. Please wait for admin approval.'];
                return back()->withNotify($notify);
            }

            if ($milestone->escrow->status != Status::ESCROW_ACCEPTED) {
                $notify[] = ['error', 'You can only pay for a milestone when its escrow status is accepted'];
                return back()->withNotify($notify);
            }

            if (($milestone->escrow->payment_mode ?? 'system') === 'direct' && ($milestone->escrow->external_amount ?? 0) > 0) {
                $notify[] = ['error', 'Milestones are not available for direct-payment escrows. Please pay the escrow service fee instead.'];
                return back()->withNotify($notify);
            }

            $user = auth()->user();

            if ($request->pay_via == 2) {
                session()->put('checkout', encrypt([
                    'amount'       => $milestone->amount,
                    'milestone_id' => $milestone->id,
                ]));

                return redirect()->route('user.deposit.index', 'checkout');
            }

            // Check balance with helpful message
            if ($user->balance < $milestone->amount) {
                $shortfall = $milestone->amount - $user->balance;
                // Keep the validation, but avoid "deposit/top up" nudges in the message.
                $notify[] = ['error', 'Insufficient balance. You need ' . showAmount($milestone->amount) . ' but only have ' . showAmount($user->balance) . '.'];
                return back()->withNotify($notify);
            }

            // Warn if paying most of balance
            $balanceAfter = $user->balance - $milestone->amount;
            if ($balanceAfter < ($user->balance * 0.1) && $balanceAfter > 0) {
                $notify[] = ['warning', 'This payment will leave you with ' . showAmount($balanceAfter) . ' remaining balance'];
                // Don't block, just warn
            }

            // Lock user and milestone to prevent concurrent payments
            DB::beginTransaction();
            
            try {
                // Reload user with lock
                $user = \App\Models\User::lockForUpdate()->find($user->id);
                
                // Re-check balance after lock (may have changed)
                if ($user->balance < $milestone->amount) {
                    DB::rollBack();
                    $shortfall = $milestone->amount - $user->balance;
                    // Keep the validation, but avoid "deposit/top up" nudges in the message.
                    $notify[] = ['error', 'Insufficient balance. Your balance changed. You need ' . showAmount($milestone->amount) . ' but only have ' . showAmount($user->balance) . '.'];
                    return back()->withNotify($notify);
                }

                $user->balance -= $milestone->amount;
                $user->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $user->id;
                $transaction->amount       = $milestone->amount;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->remark       = "milestone_paid";
                $transaction->details      = 'Milestone amount paid';
                $transaction->trx          = getTrx();
                $transaction->save();

                $milestone->payment_status = Status::MILESTONE_FUNDED;
                $milestone->save();

                $escrow               = $milestone->escrow;
                $escrow->paid_amount += $milestone->amount;
                $escrow->save();

                DB::commit();

                // Check if this was the last milestone (all milestones are now funded)
                $escrow->refresh();
                $allMilestonesFunded = $escrow->milestones()
                    ->where('payment_status', '!=', \App\Constants\Status::MILESTONE_FUNDED)
                    ->count() == 0;
                
                $totalAmount = $escrow->amount + $escrow->buyer_charge;
                $isFullyPaid = $escrow->paid_amount >= $totalAmount;

                // If all milestones are funded and escrow is fully paid, remind buyer to release
                if ($allMilestonesFunded && $isFullyPaid) {
                    $listingTitle = $escrow->listing ? $escrow->listing->title : null;
                    $user->notify(new \App\Notifications\PaymentCompleteReleaseReminder(
                        $escrow,
                        showAmount($totalAmount),
                        $listingTitle
                    ));
                    $notify[] = ['success', 'Milestone amount paid successfully. All payments are complete. Please remember to release the funds to the seller once the transaction is complete.'];
                } else {
                    $notify[] = ['success', 'Milestone amount paid successfully'];
                }
                
                return back()->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Milestone payment failed: ' . $e->getMessage(), [
                    'milestone_id' => $id,
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Milestone payment error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while processing the payment. Please try again.'];
            return back()->withNotify($notify);
        }
    }
}
