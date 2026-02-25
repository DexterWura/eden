<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Escrow;
use App\Models\EscrowCharge;
use App\Models\Message;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EscrowController extends Controller
{

    public function index(Request $request, $type = null)
    {
        $pageTitle = 'My Purchases';
        
        // Only show marketplace-related escrows (escrows linked to listings)
        $userListingEscrowIds = \App\Models\Listing::where(function($q) {
            $q->where('user_id', auth()->id())->orWhere('winner_id', auth()->id());
        })->where('escrow_id', '>', 0)->pluck('escrow_id');
        
        $escrows = Escrow::where(function ($query) {
            $query->orWhere('buyer_id', auth()->id())->orWhere('seller_id', auth()->id());
        })
        ->whereIn('id', $userListingEscrowIds)
        ->with('seller', 'buyer', 'listing');

        // Filter by status
        $status = $request->get('status');
        if ($status) {
            switch ($status) {
                case 'accepted':
                    $escrows = $escrows->accepted();
                    break;
                case 'not_accepted':
                    $escrows = $escrows->notAccepted();
                    break;
                case 'completed':
                    $escrows = $escrows->completed();
                    break;
                case 'disputed':
                    $escrows = $escrows->disputed();
                    break;
                case 'cancelled':
                    $escrows = $escrows->canceled();
                    break;
            }
        } elseif ($type) {
            // Support legacy type parameter for backward compatibility
            try {
                $escrows = $escrows->$type();
            } catch (Exception $e) {
                abort(404);
            }
        }

        // Filter by role (buying or selling)
        $role = $request->get('role');
        if ($role == 'buying') {
            $escrows = $escrows->where('buyer_id', auth()->id());
        } elseif ($role == 'selling') {
            $escrows = $escrows->where('seller_id', auth()->id());
        }

        // Search filter
        $search = $request->get('search');
        if ($search) {
            $escrows = $escrows->where(function($query) use ($search) {
                $query->where('escrow_number', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%")
                      ->orWhereHas('listing', function($q) use ($search) {
                          $q->where('title', 'like', "%{$search}%")
                            ->orWhere('listing_number', 'like', "%{$search}%");
                      });
            });
        }

        $escrows = $escrows->orderBy('id', 'desc')->with('category')->paginate(getPaginate());
        $escrows->appends($request->all());
        
        return view('Template::user.escrow.index', compact('pageTitle', 'escrows'));
    }

    public function stepOne()
    {
        $pageTitle = "New Escrow - Step One";
        return view('Template::user.escrow.step_one', compact('pageTitle'));
    }

    public function submitStepOne(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:1,2',
            'amount'      => 'required|numeric|gt:0|min:1',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Validate amount is reasonable (minimum $1)
        if ($request->amount < 1) {
            $notify[] = ['error', 'Escrow amount must be at least $1'];
            return back()->withInput()->withNotify($notify);
        }

        // Check user balance if they're the buyer
        $user = auth()->user();
        $charge = $this->getCharge($request->amount);
        $totalNeeded = $request->amount + $charge;
        
        if ($request->type == 2) { // User is buyer
            if ($user->balance < $totalNeeded) {
                $notify[] = ['error', 'Insufficient balance. You need ' . showAmount($totalNeeded) . ' (including fees). Current balance: ' . showAmount($user->balance)];
                return back()->withInput()->withNotify($notify);
            }
        }

        $data = $request->except('_token');
        $data['charge'] = $charge;
        $data['created_at'] = now()->toDateTimeString(); // Track when session was created

        session()->put('escrow_info', $data);

        return redirect()->route('user.escrow.step.two');
    }

    public function stepTwo()
    {
        $escrowInfo = session('escrow_info');
        $pageTitle  = "New Escrow - Step Two";

        if (!$escrowInfo) {
            $notify[] = ['error', 'Session expired. Please start over.'];
            return redirect()->route('user.escrow.step.one')->withNotify($notify);
        }

        // Validate session data is still valid
        if (!isset($escrowInfo['amount']) || $escrowInfo['amount'] <= 0) {
            $notify[] = ['error', 'Invalid escrow amount. Please start over.'];
            return redirect()->route('user.escrow.step.one')->withNotify($notify);
        }

        // Recalculate charge in case settings changed
        $escrowInfo['charge'] = $this->getCharge($escrowInfo['amount']);

        return view('Template::user.escrow.step_two', compact('pageTitle', 'escrowInfo'));
    }

    public function submitStepTwo(Request $request)
    {
        try {
            $request->validate([
                'email'        => 'required|max:40',
                'title'        => 'required|max:255',
                'details'      => 'required',
                'charge_payer' => 'required|in:1,2,3',
            ]);

            $this->checkSessionData($request->email);

            $escrowInfo  = session('escrow_info');
            $category_id = $escrowInfo['category_id'];
            $user        = auth()->user();
            $toUser      = User::where('email', $request->email)->first();
            
            if (!$toUser) {
                throw ValidationException::withMessages(['error' => 'User not found with the provided email']);
            }
            
            $amount      = $escrowInfo['amount'];
            $charge      = $this->getCharge($amount);

            $sellerCharge = 0;
            $buyerCharge  = 0;

            if ($request->charge_payer == 1) {
                $sellerCharge = $charge;
            } elseif ($request->charge_payer == 2) {
                $buyerCharge = $charge;
            } else {
                $sellerCharge = $charge / 2;
                $buyerCharge  = $charge / 2;
            }

            DB::beginTransaction();
            
            try {
                $escrow = new Escrow();

                if ($escrowInfo['type'] == 1) {
                    $escrow->seller_id = $user->id;
                    $escrow->buyer_id  = $toUser->id;
                } else {
                    $escrow->buyer_id  = $user->id;
                    $escrow->seller_id = $toUser->id;
                }

                $escrow->escrow_number = getTrx();
                $escrow->creator_id    = $user->id;
                $escrow->amount        = $amount;
                $escrow->charge_payer  = $request->charge_payer;
                $escrow->charge        = $charge;
                $escrow->buyer_charge  = $buyerCharge;
                $escrow->seller_charge = $sellerCharge;
                $escrow->category_id   = $category_id;
                $escrow->title         = $request->title;
                $escrow->details       = $request->details;

                if (!$toUser) {
                    $escrow->invitation_mail = $request->email;
                }

                $escrow->save();

                $conversation            = new Conversation();
                $conversation->escrow_id = $escrow->id;
                $conversation->buyer_id  = $escrow->buyer_id;
                $conversation->seller_id = $escrow->seller_id;
                $conversation->save();

                // Log escrow creation
                \Log::info('Escrow created', [
                    'escrow_id' => $escrow->id,
                    'escrow_number' => $escrow->escrow_number,
                    'creator_id' => $user->id,
                    'creator_username' => $user->username,
                    'buyer_id' => $escrow->buyer_id,
                    'seller_id' => $escrow->seller_id,
                    'amount' => $amount,
                    'charge' => $charge,
                    'title' => $request->title,
                    'category_id' => $category_id,
                    'invited_user' => $toUser ? null : $request->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                DB::commit();

                $message = 'Escrow created successfully';

                if (!$toUser) {
                    $inviteUser = (object) [
                        'fullname' => $request->email,
                        'username' => $request->email,
                        'email'    => $request->email,
                    ];

                    notify($inviteUser, 'INVITATION_LINK', [
                        'link' => route('user.register') . "?invite_email=" . $request->email,
                    ], ['email']);

                    $message = 'Escrow created and invitation link sent successfully';
                }

                session()->forget('escrow_info');
                $notify[] = ['success', $message];

                return redirect()->route('user.escrow.index')->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Escrow creation failed: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'email' => $request->email,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Escrow creation error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while creating the escrow. Please try again.'];
            return back()->withNotify($notify)->withInput();
        }
    }

    public function details($id)
    {
        $pageTitle    = "Purchase Details";
        $escrow       = Escrow::checkUser()->with('conversation.messages.sender', 'conversation.messages.admin', 'listing')->findOrFail($id);
        $conversation = $escrow->conversation;
        $messages     = $conversation->messages;
        $canLeaveReview = $escrow->status == Status::ESCROW_COMPLETED
            && $escrow->buyer_id == auth()->id()
            && $escrow->listing_id
            && !Review::where('escrow_id', $escrow->id)->where('reviewer_id', auth()->id())->where('review_type', 'buyer_review')->exists();
        $hasLeftReview = Review::where('escrow_id', $escrow->id)->where('reviewer_id', auth()->id())->where('review_type', 'buyer_review')->exists();
        return view('Template::user.escrow.details', compact('pageTitle', 'escrow', 'conversation', 'messages', 'canLeaveReview', 'hasLeftReview'));
    }

    public function replyMessage(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'message'         => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()]);
        }

        $conversation = Conversation::where('id', $request->conversation_id)->checkUser()->active()->first();

        if (!$conversation) {
            return response()->json(['error' => ['Conversation not found']]);
        }

        $message                  = new Message();
        $message->sender_id       = auth()->id();
        $message->conversation_id = $conversation->id;
        $message->message         = $request->message;
        $message->save();
        
        // Notify the other party in the escrow
        $escrow = $conversation->escrow;
        $otherUser = $escrow->buyer_id == auth()->id() ? $escrow->seller : $escrow->buyer;
        if ($otherUser) {
            $otherUser->notify(new \App\Notifications\EscrowMessageReceived($escrow, $message, auth()->user()));
        }

        return [
            'created_diff' => $message->created_at->diffForHumans(),
            'created_time' => $message->created_at->format('h:i A'),
            'message'      => $message->message,
        ];
    }

    public function getMessages(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()]);
        }

        $conversation = Conversation::where('id', $request->conversation_id)->where(function ($query) {
            $query->orWhere('buyer_id', auth()->id())->orWhere('seller_id', auth()->id());
        })->first();

        if (!$conversation) {
            return response()->json(['error' => ['Conversation not found']]);
        }

        $escrow   = $conversation->escrow;
        $messages = Message::where('conversation_id', $conversation->id)->with('sender', 'admin')->get();
        return view('Template::user.escrow.message', compact('messages', 'escrow'));
    }

    public function cancel($id)
    {
        $escrow         = Escrow::checkUser()->notAccepted()->findOrFail($id);
        $escrow->status = Status::ESCROW_CANCELLED;
        $escrow->save();

        // Clear escrow_id from listing if it exists, so listing can appear in marketplace again
        $listing = \App\Models\Listing::where('escrow_id', $escrow->id)->first();
        if ($listing && $listing->status === Status::LISTING_ACTIVE) {
            $listing->escrow_id = null;
            $listing->winner_id = null;
            $listing->final_price = null;
            $listing->save();
        }

        if ($escrow->buyer_id == auth()->id()) {
            $mailReceiver = $escrow->seller;
            $canceller    = 'buyer';
        } else {
            $mailReceiver = $escrow->buyer;
            $canceller    = 'seller';
        }

        $conversation         = $escrow->conversation;
        $conversation->status = Status::CONVERSION_CLOSE;
        $conversation->save();

        if ($mailReceiver) {
            notify($mailReceiver, 'ESCROW_CANCELLED', [
                'title'      => $escrow->title,
                'amount'     => showAmount($escrow->amount, currencyFormat: false),
                'canceller'  => $canceller,
                'total_fund' => $escrow->paid_amount,
                'currency'   => gs()->cur_text,
                'escrow_url' => url(route('user.escrow.details', $escrow->id)),
            ]);
        }

        $notify[] = ['success', 'Escrow cancelled successfully'];
        return back()->withNotify($notify);
    }

    public function accept($id)
    {
        try {
            $escrow = Escrow::checkUser()->where('creator_id', '!=', auth()->id())->notAccepted()->findOrFail($id);
            $user = auth()->user();

            // Additional security validation
            if ($escrow->buyer_id !== $user->id && $escrow->seller_id !== $user->id) {
                Log::warning('Unauthorized escrow access attempt', [
                    'escrow_id' => $id,
                    'user_id' => $user->id,
                    'buyer_id' => $escrow->buyer_id,
                    'seller_id' => $escrow->seller_id
                ]);
                abort(403, 'Unauthorized access to escrow');
            }

            // Check if already accepted
            if ($escrow->status === Status::ESCROW_ACCEPTED) {
                $notify[] = ['error', 'Escrow has already been accepted'];
                return back()->withNotify($notify);
            }

            // Prevent accepting escrows that are disputed or cancelled
            if (in_array($escrow->status, [Status::ESCROW_DISPUTED, Status::ESCROW_CANCELLED])) {
                $notify[] = ['error', 'Cannot accept an escrow that is disputed or cancelled'];
                return back()->withNotify($notify);
            }

            // For buyers: Check balance before accepting
            if ($escrow->buyer_id == $user->id) {
                $totalNeeded = $escrow->amount + $escrow->buyer_charge;
                if ($user->balance < $totalNeeded) {
                    $shortfall = $totalNeeded - $user->balance;
                    // Keep the validation, but avoid "deposit/top up" nudges in the message.
                    $notify[] = ['error', 'Insufficient balance to accept escrow. You need ' . showAmount($totalNeeded) . ' (including fees) but only have ' . showAmount($user->balance) . '.'];
                    return back()->withNotify($notify);
                }

                // Additional validation: prevent buyers from accepting their own escrows
                if ($escrow->creator_id === $user->id) {
                    $notify[] = ['error', 'You cannot accept an escrow you created yourself'];
                    return back()->withNotify($notify);
                }
            }

            // For sellers: basic validation
            if ($escrow->seller_id == $user->id) {
                // Sellers can only accept escrows they didn't create
                if ($escrow->creator_id === $user->id) {
                    $notify[] = ['error', 'Invalid escrow acceptance request'];
                    return back()->withNotify($notify);
                }
            }

            DB::beginTransaction();
            
            try {
                $escrow->status = Status::ESCROW_ACCEPTED;
                if (\Illuminate\Support\Facades\Schema::hasColumn('escrows', 'accepted_at')) {
                    $escrow->accepted_at = now();
                }
                $escrow->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Escrow acceptance failed: ' . $e->getMessage(), [
                    'escrow_id' => $id,
                    'user_id' => auth()->id(),
                ]);
                throw $e;
            }

            if ($escrow->buyer_id == auth()->id()) {
                $mailReceiver = $escrow->seller;
                $accepter     = 'buyer';
            } else {
                $mailReceiver = $escrow->buyer;
                $accepter     = 'seller';
            }

            notify($mailReceiver, 'ESCROW_ACCEPTED', [
                'title'      => $escrow->title,
                'amount'     => showAmount($escrow->amount, currencyFormat: false),
                'accepter'   => $accepter,
                'total_fund' => showAmount($escrow->paid_amount, currencyFormat: false),
                'currency'   => gs()->cur_text,
                'escrow_url' => url(route('user.escrow.details', $escrow->id)),
            ]);

            notifySuperAdminsForEscrowAccepted($escrow);

            $notify[] = ['success', 'Escrow accepted successfully. You can now proceed with payment.'];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            Log::error('Escrow acceptance error: ' . $e->getMessage(), [
                'escrow_id' => $id,
                'user_id' => auth()->id(),
            ]);
            $notify[] = ['error', 'An error occurred while accepting the escrow. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function dispute(Request $request, $id)
    {
        $request->validate([
            'dispute_reason' => 'required|string|min:20|max:2000',
        ], [
            'dispute_reason.required' => 'Dispute reason is required',
            'dispute_reason.min' => 'Please provide a detailed reason (at least 20 characters)',
            'dispute_reason.max' => 'Dispute reason cannot exceed 2000 characters',
        ]);

        $escrow = Escrow::checkUser()->accepted()->findOrFail($id);
        $user = auth()->user();

        // Additional security validation
        if ($escrow->buyer_id !== $user->id && $escrow->seller_id !== $user->id) {
            Log::warning('Unauthorized escrow dispute attempt', [
                'escrow_id' => $id,
                'user_id' => $user->id,
                'buyer_id' => $escrow->buyer_id,
                'seller_id' => $escrow->seller_id
            ]);
            abort(403, 'Unauthorized access to escrow');
        }

        // Check if already disputed
        if ($escrow->status == Status::ESCROW_DISPUTED) {
            $notify[] = ['error', 'This escrow is already in dispute'];
            return back()->withNotify($notify);
        }

        // Check if already completed
        if ($escrow->status == Status::ESCROW_COMPLETED) {
            $notify[] = ['error', 'Cannot dispute a completed escrow'];
            return back()->withNotify($notify);
        }

        // Prevent disputing cancelled escrows
        if ($escrow->status == Status::ESCROW_CANCELLED) {
            $notify[] = ['error', 'Cannot dispute a cancelled escrow'];
            return back()->withNotify($notify);
        }

        // Common sense: Prevent disputing escrows with no funds paid
        if ($escrow->paid_amount == 0) {
            $notify[] = ['error', 'Cannot dispute escrow - no payment has been made yet. Please cancel the escrow instead.'];
            return back()->withNotify($notify);
        }

        // Rate limiting for disputes - prevent spam disputes
        $recentDisputes = Escrow::where('disputer_id', $user->id)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        if ($recentDisputes >= 5) { // Max 5 disputes per 24 hours
            $notify[] = ['error', 'You have reached the maximum number of disputes allowed per day. Please contact support.'];
            return back()->withNotify($notify);
        }

        $escrow->status       = Status::ESCROW_DISPUTED;
        $escrow->disputer_id  = auth()->id();
        $escrow->dispute_note = trim($request->dispute_reason);
        $escrow->save();

        // Log dispute creation
        \Log::warning('Escrow dispute created', [
            'escrow_id' => $escrow->id,
            'escrow_number' => $escrow->escrow_number,
            'disputer_id' => $user->id,
            'disputer_username' => $user->username,
            'buyer_id' => $escrow->buyer_id,
            'seller_id' => $escrow->seller_id,
            'amount' => $escrow->amount,
            'paid_amount' => $escrow->paid_amount,
            'dispute_reason_length' => strlen($request->dispute_reason),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String()
        ]);

        $conversation           = $escrow->conversation;
        $conversation->is_group = 1;
        $conversation->save();

        if ($escrow->buyer_id == auth()->id()) {
            $mailReceiver = $escrow->seller;
            $disputer     = 'buyer';
        } else {
            $mailReceiver = $escrow->buyer;
            $disputer     = 'seller';
        }

        notify($mailReceiver, 'ESCROW_DISPUTED', [
            'title'        => $escrow->title,
            'amount'       => showAmount($escrow->amount, currencyFormat: false),
            'disputer'     => $disputer,
            'total_fund'   => showAmount($escrow->paid_amount, currencyFormat: false),
            'dispute_note' => $request->details ?? $request->dispute_reason ?? '',
            'currency'     => gs()->cur_text,
            'escrow_url'   => url(route('user.escrow.details', $escrow->id)),
        ]);

        $notify[] = ['success', 'Escrow disputed successfully'];
        return back()->withNotify($notify);
    }

    public function dispatchEscrow($id)
    {
        try {
            // Lock escrow and seller to prevent concurrent dispatches
            $escrow = Escrow::lockForUpdate()
                ->where('buyer_id', auth()->id())
                ->accepted()
                ->with(['seller' => function($q) {
                    $q->lockForUpdate();
                }])
                ->findOrFail($id);

            // Check if already dispatched
            if ($escrow->status === Status::ESCROW_COMPLETED) {
                $notify[] = ['error', 'Escrow payment has already been dispatched'];
                return back()->withNotify($notify);
            }

            DB::beginTransaction();
            
            try {
                $escrow->status = Status::ESCROW_COMPLETED;
                $escrow->save();

                $amount           = $escrow->amount;
                $saleAmount       = ($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0
                    ? $escrow->external_amount
                    : $escrow->amount;
                $seller           = $escrow->seller;
                if ($amount > 0) {
                    $seller->balance += $amount;
                    $seller->save();
                }

                // Mark associated listing as SOLD if it exists
                $listing = \App\Models\Listing::where('escrow_id', $escrow->id)->first();
                if ($listing && $listing->status !== Status::LISTING_SOLD) {
                    $listing->status = Status::LISTING_SOLD;
                    $listing->sold_at = now();
                    $listing->save();

                    // Update user stats now that escrow is completed
                    $seller->increment('total_sales');
                    $seller->increment('total_sales_value', $saleAmount);
                    if ($escrow->buyer) {
                        $escrow->buyer->increment('total_purchases');
                    }
                }

                $trx = getTrx();
                if ($amount > 0) {
                    $transaction               = new Transaction();
                    $transaction->user_id      = $seller->id;
                    $transaction->amount       = $amount;
                    $transaction->post_balance = $seller->balance;
                    $transaction->charge       = 0;
                    $transaction->trx_type     = '+';
                    $transaction->remark       = "escrow_payment_dispatched";
                    $transaction->details      = 'Escrow payment dispatched';
                    $transaction->trx          = $trx;
                    $transaction->save();
                }

                if ($escrow->seller_charge && $escrow->seller_charge > 0) {
                    $seller->balance -= $escrow->seller_charge;
                    $seller->save();

                    $transaction               = new Transaction();
                    $transaction->user_id      = $seller->id;
                    $transaction->amount       = $escrow->seller_charge;
                    $transaction->post_balance = $seller->balance;
                    $transaction->charge       = 0;
                    $transaction->trx_type     = '-';
                    $transaction->remark       = "escrow_charge";
                    $transaction->details      = 'Deducted as escrow charge';
                    $transaction->trx          = $trx;
                    $transaction->save();
                }

                // Log escrow dispatch
                \Log::info('Escrow payment dispatched', [
                    'escrow_id' => $escrow->id,
                    'escrow_number' => $escrow->escrow_number,
                    'buyer_id' => auth()->id(),
                    'seller_id' => $seller->id,
                    'amount' => $amount,
                    'sale_amount' => $saleAmount,
                    'payment_mode' => $escrow->payment_mode ?? 'system',
                    'seller_charge' => $escrow->seller_charge,
                    'transaction_id' => $trx,
                    'listing_id' => $listing ? $listing->id : null,
                    'timestamp' => now()->toIso8601String()
                ]);

                DB::commit();

                notifySuperAdminsForEscrowCompleted($escrow);

                // Notify seller (outside transaction)
                $isDirect = (($escrow->payment_mode ?? 'system') === 'direct') && (($escrow->external_amount ?? 0) > 0);
                if ($isDirect) {
                    notify($seller, 'DIRECT_ESCROW_MARKED_COMPLETE', [
                        'title' => $escrow->title,
                        'escrow_number' => $escrow->escrow_number,
                        'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                        'sale_amount' => showAmount($escrow->external_amount, currencyFormat: false),
                        'fee_amount' => showAmount($escrow->buyer_charge, currencyFormat: false),
                        'currency' => gs()->cur_text,
                    ]);
                    $notify[] = ['success', 'Escrow marked complete successfully'];
                } else {
                    notify($seller, 'ESCROW_PAYMENT_DISPATCHED', [
                        'title'         => $escrow->title,
                        'amount'        => showAmount($saleAmount, currencyFormat: false),
                        'charge'        => showAmount($escrow->charge, currencyFormat: false),
                        'seller_charge' => showAmount($escrow->seller_charge),
                        'trx'           => $trx,
                        'post_balance'  => showAmount($seller->balance, currencyFormat: false),
                        'currency'      => gs()->cur_text,
                        'escrow_url'    => url(route('user.escrow.details', $escrow->id)),
                    ]);
                    $notify[] = ['success', 'Escrow payment dispatched successfully'];
                }
                return back()->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Escrow dispatch failed: ' . $e->getMessage(), [
                    'escrow_id' => $id,
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Escrow dispatch error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while dispatching the payment. Please try again.'];
            return back()->withNotify($notify);
        }
    }


    private function checkSessionData($email)
    {
        $user       = auth()->user();
        $escrowInfo = session('escrow_info');

        if (!$escrowInfo) {
            throw ValidationException::withMessages(['error' => 'Session expired. Please start over.']);
        }

        // Check if session is too old (more than 30 minutes)
        if (isset($escrowInfo['created_at'])) {
            $createdAt = \Carbon\Carbon::parse($escrowInfo['created_at']);
            if ($createdAt->diffInMinutes(now()) > 30) {
                session()->forget('escrow_info');
                throw ValidationException::withMessages(['error' => 'Session expired. Please start over.']);
            }
        }

        if ($user->email == $email) {
            throw ValidationException::withMessages(['error' => 'You cannot create escrow with yourself']);
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['error' => 'Invalid email address']);
        }

        // Check if user exists
        $otherUser = User::where('email', $email)->first();
        if (!$otherUser) {
            throw ValidationException::withMessages(['error' => 'User with this email does not exist']);
        }

        // Check if user is active
        if ($otherUser->status != Status::USER_ACTIVE) {
            throw ValidationException::withMessages(['error' => 'Cannot create escrow with a banned or inactive user']);
        }

        $category = Category::active()->where('id', $escrowInfo['category_id'])->first();

        if (!$category) {
            throw ValidationException::withMessages(['error' => 'Invalid escrow type']);
        }

        // Validate amount is still reasonable
        if (!isset($escrowInfo['amount']) || $escrowInfo['amount'] <= 0) {
            throw ValidationException::withMessages(['error' => 'Invalid escrow amount']);
        }
    }

    private function getCharge($amount)
    {
        $general       = gs();
        $percentCharge = $general->percent_charge;
        $fixedCharge   = $general->fixed_charge;
        $escrowCharge  = EscrowCharge::where('minimum', '<=', $amount)->where('maximum', '>=', $amount)->first();

        if ($escrowCharge) {
            $percentCharge = $escrowCharge->percent_charge;
            $fixedCharge   = $escrowCharge->fixed_charge;
        }

        $charge = $amount * $percentCharge / 100 + $fixedCharge;


        if ($charge && $charge > $general->charge_cap) {
            $charge = $general->charge_cap;
        }

        return $charge;
    }

    /**
     * Pay full escrow amount (without milestones)
     */
    public function payFull(Request $request, $id)
    {
        try {
            $request->validate([
                'pay_via' => 'required|in:1,2',
            ]);

            $escrow = Escrow::lockForUpdate()
                ->where('buyer_id', auth()->id())
                ->accepted()
                ->with(['seller', 'milestones'])
                ->findOrFail($id);

            // Check if escrow has funded milestones (can't pay full if any milestone is already funded)
            $fundedMilestones = $escrow->milestones->where('payment_status', Status::MILESTONE_FUNDED);
            if ($fundedMilestones->count() > 0) {
                $notify[] = ['error', 'Cannot pay full amount because some milestones have already been paid. Please continue paying through milestones.'];
                return back()->withNotify($notify);
            }
            
            // If milestones exist but none are funded, delete them when paying full
            if ($escrow->milestones->count() > 0) {
                // Delete all unfunded milestones when paying full
                $escrow->milestones()->delete();
            }

            $totalAmount = $escrow->amount + $escrow->buyer_charge;
            $remainingAmount = $totalAmount - $escrow->paid_amount;
            $isDirect = (($escrow->payment_mode ?? 'system') === 'direct') && (($escrow->external_amount ?? 0) > 0);

            // Direct payout: there is no in-platform payment required from the buyer.
            // Buyer pays the seller outside the platform using the external payment link.
            if ($isDirect) {
                $notify[] = ['error', 'No platform payment is required for Direct payout. Please pay the seller using the External Payment Link, then mark the escrow as complete.'];
                return back()->withNotify($notify);
            }

            if ($remainingAmount <= 0) {
                $notify[] = ['error', 'Escrow is already fully paid'];
                return back()->withNotify($notify);
            }

            $user = auth()->user();

            // Pay via direct payment (gateway)
            if ($request->pay_via == 2) {
                session()->put('checkout', encrypt([
                    'amount' => $remainingAmount,
                    'escrow_id' => $escrow->id,
                    'type' => 'escrow_full_payment',
                ]));

                return redirect()->route('user.deposit.index', 'checkout');
            }

            // Pay via wallet
            if ($user->balance < $remainingAmount) {
                // Keep the validation, but avoid "deposit/top up" nudges in the message.
                $notify[] = ['error', 'You have insufficient balance.'];
                return back()->withNotify($notify);
            }

            DB::beginTransaction();
            
            try {
                // Deduct from buyer balance
                $user->balance -= $remainingAmount;
                $user->save();

                // Update escrow paid amount
                $escrow->paid_amount += $remainingAmount;
                $escrow->save();

                // Create transaction record
                $trx = getTrx();
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount = $remainingAmount;
                $transaction->post_balance = $user->balance;
                $transaction->charge = 0;
                $transaction->trx_type = '-';
                $transaction->remark = 'escrow_payment';
                $transaction->details = $isDirect
                    ? 'Escrow service fee payment for escrow: ' . $escrow->escrow_number
                    : 'Full payment for escrow: ' . $escrow->escrow_number;
                $transaction->trx = $trx;
                $transaction->save();

                DB::commit();

                // Reload escrow to get updated data
                $escrow->refresh();
                $totalAmount = $escrow->amount + $escrow->buyer_charge;
                $listingTitle = $escrow->listing ? $escrow->listing->title : null;

                // Notify seller
                if ($isDirect) {
                    notify($escrow->seller, 'DIRECT_ESCROW_SERVICE_FEE_PAID', [
                        'escrow_number' => $escrow->escrow_number,
                        'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                        'fee_amount' => showAmount($escrow->buyer_charge, currencyFormat: false),
                        'sale_amount' => showAmount($escrow->external_amount, currencyFormat: false),
                        'currency' => gs()->cur_text,
                        'listing_title' => $listingTitle ?? $escrow->title,
                        'buyer' => $user->username,
                    ]);
                } else {
                    notify($escrow->seller, 'ESCROW_FULLY_PAID', [
                        'escrow_number' => $escrow->escrow_number,
                        'escrow_url' => url(route('user.escrow.details', $escrow->id)),
                        'amount' => showAmount($remainingAmount, currencyFormat: false),
                        'currency' => gs()->cur_text,
                    ]);
                }

                // Track escrow payment to DataFast
                \App\Services\DatafastService::trackPayment(
                    amount: (float) $remainingAmount,
                    currency: gs()->cur_text,
                    transactionId: $trx,
                    options: [
                        'datafast_visitor_id' => \App\Services\DatafastService::getVisitorId(),
                        'email' => $user->email,
                        'name' => $user->fullname ?? $user->username,
                        'customer_id' => (string) $user->id,
                    ]
                );

                // Notify buyer to release funds (database notification for top bar)
                $user->notify(new \App\Notifications\PaymentCompleteReleaseReminder(
                    $escrow,
                    showAmount($totalAmount),
                    $listingTitle
                ));

                $notify[] = [
                    'success',
                    $isDirect
                        ? 'Escrow service fee paid successfully. Next, pay the seller using the External Payment Link and then mark the escrow as complete.'
                        : 'Payment completed successfully. Please remember to release the funds to the seller once the transaction is complete.',
                ];
                return back()->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Full escrow payment failed: ' . $e->getMessage(), [
                    'escrow_id' => $id,
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Full escrow payment error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while processing payment. Please try again.'];
            return back()->withNotify($notify);
        }
    }
}
