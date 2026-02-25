<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\DeviceToken;
use App\Models\Escrow;
use App\Models\Form;
use App\Models\Transaction;
use App\Models\Listing;
use App\Models\Bid;
use App\Models\Offer;
use App\Models\Watchlist;
use App\Models\Milestone;
use App\Models\NdaDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function home()
    {
        $pageTitle = 'Dashboard';
        $user      = auth()->user();

        // Marketplace Statistics (Primary Focus)
        $data['balance'] = $user->balance;
        
        // My Listings
        $data['my_listings'] = Listing::where('user_id', $user->id)->count();
        $data['active_listings'] = Listing::where('user_id', $user->id)
            ->where('status', Status::LISTING_ACTIVE)->count();
        $data['sold_listings'] = Listing::where('user_id', $user->id)
            ->where('status', Status::LISTING_SOLD)->count();
        $data['pending_listings'] = Listing::where('user_id', $user->id)
            ->where('status', Status::LISTING_PENDING)->count();
        $data['total_sales_value'] = Listing::where('user_id', $user->id)
            ->where('status', Status::LISTING_SOLD)->sum('final_price');
        $data['total_listing_views'] = Listing::where('user_id', $user->id)->sum('view_count');
        
        // My Bids
        $data['my_bids'] = Bid::where('user_id', $user->id)->count();
        $data['winning_bids'] = Bid::where('user_id', $user->id)
            ->where('status', Status::BID_WINNING)->count();
        $data['won_bids'] = Bid::where('user_id', $user->id)
            ->where('status', Status::BID_WON)->count();
        
        // My Offers
        $data['my_offers'] = Offer::where('buyer_id', $user->id)->count();
        $data['pending_offers'] = Offer::where('buyer_id', $user->id)
            ->where('status', Status::OFFER_PENDING)->count();
        $data['accepted_offers'] = Offer::where('buyer_id', $user->id)
            ->where('status', Status::OFFER_ACCEPTED)->count();
        
        // Watchlist
        $data['watchlist_items'] = Watchlist::where('user_id', $user->id)->count();

        // Signed NDAs
        $data['signed_ndas'] = NdaDocument::where('user_id', $user->id)->count();
        
        // Escrow (only marketplace-related)
        $userListingEscrowIds = Listing::where(function($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('winner_id', $user->id);
        })->where('escrow_id', '>', 0)->pluck('escrow_id');
        
        $escrow = Escrow::where(function ($query) use ($user) {
            $query->orWhere('buyer_id', $user->id)->orWhere('seller_id', $user->id);
        })->whereIn('id', $userListingEscrowIds);
        
        $data['active_escrows'] = (clone $escrow)->accepted()->count();
        $data['completed_escrows'] = (clone $escrow)->completed()->count();

        // Financial (secondary)
        $data['pendingDeposit'] = $user->deposits()->pending()->count();
        $data['pendingWithdrawals'] = $user->withdrawals()->pending()->count();

        $transactions = Transaction::where('user_id', auth()->id())->latest()->limit(5)->get();
        
        // Get pending escrow actions for reminders
        $pendingActions = $this->getPendingEscrowActions($user);
        
        // Affiliate: show referral link and stats when enabled
        $affiliateEnabled = (int) (gs('affiliate_enable') ?? 0);
        $referralLink = $affiliateEnabled ? url('/ref/' . $user->username) : null;
        $referralCount = 0;
        $referralEarned = 0;
        $referredUsers = collect();
        if ($affiliateEnabled) {
            $referralCount = $user->referredUsers()->count();
            $referralEarned = Transaction::where('user_id', $user->id)->where('remark', 'affiliate_signup')->sum('amount');
        }
        $referredUsers = collect();

        return view('Template::user.dashboard', compact('pageTitle', 'user', 'transactions', 'data', 'pendingActions', 'affiliateEnabled', 'referralLink', 'referralCount', 'referralEarned', 'referredUsers'));

    }

    /**
     * Return referred users list for dashboard (loaded on expand).
     */
    public function getReferredUsers(Request $request)
    {
        if (!(int) (gs('affiliate_enable') ?? 0)) {
            return response()->json(['users' => []]);
        }
        $user = auth()->user();
        $limit = min((int) $request->input('limit', 50), 100);
        $users = $user->referredUsers()->latest()->limit($limit)->get(['id', 'username', 'created_at'])
            ->map(fn ($u) => ['username' => $u->username, 'signed_up' => $u->created_at->format('d M, Y')]);
        return response()->json(['users' => $users]);
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id','desc')->paginate(getPaginate());
        return view('Template::user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function show2faForm()
    {
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);
        $pageTitle = '2FA Security';
        return view('Template::user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'key' => 'required',
            'code' => 'required|numeric|digits:6',
        ], [
            'key.required' => 'Secret key is required',
            'code.required' => 'Verification code is required',
            'code.numeric' => 'Verification code must be numeric',
            'code.digits' => 'Verification code must be 6 digits',
        ]);

        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = Status::ENABLE;
            $user->save();
            $notify[] = ['success', 'Two-factor authentication enabled successfully. Your account is now more secure.'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Invalid verification code. Please check your authenticator app and try again.'];
            return back()->withInput()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ], [
            'code.required' => 'Verification code is required',
            'code.numeric' => 'Verification code must be numeric',
            'code.digits' => 'Verification code must be 6 digits',
        ]);

        $user = auth()->user();
        
        // Check if 2FA is actually enabled
        if (!$user->ts) {
            $notify[] = ['error', 'Two-factor authentication is not enabled'];
            return back()->withNotify($notify);
        }

        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = Status::DISABLE;
            $user->save();
            $notify[] = ['success', 'Two-factor authentication disabled successfully. Your account security has been reduced.'];
        } else {
            $notify[] = ['error', 'Invalid verification code. Please check your authenticator app and try again.'];
        }
        return back()->withInput()->withNotify($notify);
    }

    public function transactions()
    {
        
        $pageTitle    = 'Transactions';
        $remarks      = Transaction::distinct('remark')->orderBy('remark')->whereNotNull("remark")->get('remark');
        $transactions = Transaction::where('user_id',auth()->id())->searchable(['trx'])->filter(['trx_type','remark'])->orderBy('id','desc')->paginate(getPaginate());

        return view('Template::user.transactions', compact('pageTitle','transactions','remarks'));
    }

    public function kycForm()
    {
        if (auth()->user()->kv == Status::KYC_PENDING) {
            $notify[] = ['error','Your KYC is under review'];
            return to_route('user.home')->withNotify($notify);
        }
        if (auth()->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = ['error','You are already KYC verified'];
            return to_route('user.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form = Form::where('act','kyc')->first();
        return view('Template::user.kyc.form', compact('pageTitle','form'));
    }

    public function kycData()
    {
        $user = auth()->user();
        $pageTitle = 'KYC Data';
        return view('Template::user.kyc.info', compact('pageTitle','user'));
    }

    public function kycSubmit(Request $request)
    {
        $form = Form::where('act','kyc')->firstOrFail();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $user = auth()->user();
        foreach (@$user->kyc_data ?? [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify').'/'.$kycData->value);
            }
        }
        $userData = $formProcessor->processFormData($request, $formData);
        $user->kyc_data = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv = Status::KYC_PENDING;
        $user->save();

        $notify[] = ['success','KYC data submitted successfully'];
        return to_route('user.home')->withNotify($notify);

    }

    public function notificationRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    public function markAllNotificationsAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    }

    public function userData()
    {
        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $pageTitle  = 'User Data';
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        return view('Template::user.user_data', compact('pageTitle', 'user', 'countries', 'mobileCode'));
    }

    public function userDataSubmit(Request $request)
    {

        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $countryData  = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $hasStates = countryHasStates($request->country_code);
        
        $validationRules = [
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:users|min:6',
            'mobile'       => ['required','regex:/^([0-9]*)$/',Rule::unique('users')->where('dial_code',$request->mobile_code)],
        ];
        
        // Make state and zip required only if country has states
        if ($hasStates) {
            $validationRules['state'] = 'required|string|max:100';
            $validationRules['zip'] = 'required|string|max:20|regex:/^[A-Z0-9\s\-]{3,10}$/i';
        } else {
            $validationRules['state'] = 'nullable|string|max:100';
            $validationRules['zip'] = 'nullable|string|max:20';
        }
        
        $request->validate($validationRules, [
            'state.required' => 'State is required for your selected country',
            'zip.required' => 'Zip/Postal code is required for your selected country',
            'zip.regex' => 'Invalid ZIP/postal code format',
        ]);


        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        // Validate username is not a reserved word
        $reservedUsernames = ['admin', 'administrator', 'root', 'system', 'support', 'help', 'info', 'contact', 'api', 'www', 'mail', 'email'];
        if (in_array(strtolower(trim($request->username)), $reservedUsernames)) {
            $notify[] = ['error', 'This username is reserved and cannot be used'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        // Validate mobile number format
        $mobile = trim($request->mobile);
        if (strlen($mobile) < 6 || strlen($mobile) > 15) {
            $notify[] = ['error', 'Mobile number must be between 6 and 15 digits'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $user->country_code = $request->country_code;
        $user->mobile       = $mobile;
        $user->username     = strtolower(trim($request->username)); // Normalize username


        // Clean and format address fields
        $user->address = $request->address ? trim($request->address) : null;
        $user->city = $request->city ? ucwords(strtolower(trim($request->city))) : null;
        $user->country_name = $request->country ?? null;
        $user->dial_code = $request->mobile_code;
        
        // Only set state/zip if country has states, otherwise clear them
        if ($hasStates) {
            $user->state = $request->state ? ucwords(strtolower(trim($request->state))) : null;
            $user->zip = $request->zip ? strtoupper(trim($request->zip)) : null;
        } else {
            $user->state = null;
            $user->zip = null;
        }

        $user->profile_complete = Status::YES;
        $user->save();

        $notify[] = ['success', 'Profile information saved successfully'];
        return to_route('user.home')->withNotify($notify);
    }


    public function addDeviceToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()->all()];
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            return ['success' => true, 'message' => 'Already exists'];
        }

        $deviceToken          = new DeviceToken();
        $deviceToken->user_id = auth()->user()->id;
        $deviceToken->token   = $request->token;
        $deviceToken->is_app  = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
    }

    public function downloadAttachment($fileHash)
    {
        try {
            $filePath = decrypt($fileHash);
            
            if (!file_exists($filePath)) {
                $notify[] = ['error', 'File does not exist or has been removed'];
                return back()->withNotify($notify);
            }

            // Security check: ensure file is within allowed directories
            $allowedPaths = [
                storage_path('app'),
                public_path('assets'),
            ];
            
            $isAllowed = false;
            foreach ($allowedPaths as $allowedPath) {
                if (strpos($filePath, $allowedPath) === 0) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                abort(403, 'Unauthorized file access');
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $title = slug(gs('site_name')) . '- attachments.' . $extension;
            
            $mimetype = mime_content_type($filePath);
            if (!$mimetype) {
                $mimetype = 'application/octet-stream';
            }

            return response()->download($filePath, $title, [
                'Content-Type' => $mimetype,
            ]);
        } catch (\Exception $e) {
            \Log::error('File download error: ' . $e->getMessage(), [
                'file_hash' => $fileHash,
                'user_id' => auth()->id(),
            ]);
            $notify[] = ['error', 'Unable to download file. Please contact support if this persists.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Get pending escrow actions that require user attention
     */
    private function getPendingEscrowActions($user)
    {
        $actions = [];

        // Get all escrows where user is buyer or seller
        $escrows = Escrow::where(function($q) use ($user) {
            $q->where('buyer_id', $user->id)
              ->orWhere('seller_id', $user->id);
        })
        ->whereNotIn('status', [Status::ESCROW_COMPLETED, Status::ESCROW_CANCELLED])
        ->with(['listing', 'milestones'])
        ->get();

        foreach ($escrows as $escrow) {
            $isBuyer = $escrow->buyer_id == $user->id;
            $isSeller = $escrow->seller_id == $user->id;

            // 1. Escrow not accepted - buyer needs to accept (for non-marketplace escrows)
            if ($escrow->status == Status::ESCROW_NOT_ACCEPTED && $isBuyer && $escrow->creator_id != $user->id) {
                $actions[] = [
                    'type' => 'escrow_accept_buyer',
                    'message' => 'You have a pending escrow. Please accept it to proceed.',
                    'link' => route('user.escrow.details', $escrow->id),
                    'linkText' => 'View Escrow',
                    'priority' => 'high',
                    'escrow_id' => $escrow->id,
                    'listing_title' => $escrow->listing ? ($escrow->listing->title ?? $escrow->listing->domain_name ?? 'N/A') : ($escrow->title ?? 'N/A'),
                ];
            }

            // 2. Escrow not accepted - seller needs to accept (for non-marketplace escrows)
            if ($escrow->status == Status::ESCROW_NOT_ACCEPTED && $isSeller) {
                $actions[] = [
                    'type' => 'escrow_accept_seller',
                    'message' => 'A buyer has initiated payment. Please accept the escrow to proceed.',
                    'link' => route('user.escrow.details', $escrow->id),
                    'linkText' => 'View Escrow',
                    'priority' => 'high',
                    'escrow_id' => $escrow->id,
                    'listing_title' => $escrow->listing ? ($escrow->listing->title ?? $escrow->listing->domain_name ?? 'N/A') : ($escrow->title ?? 'N/A'),
                ];
            }

            // 3. Escrow accepted but buyer needs to pay
            if ($escrow->status == Status::ESCROW_ACCEPTED && $isBuyer) {
                $totalAmount = $escrow->amount + $escrow->buyer_charge;
                $remainingAmount = $totalAmount - $escrow->paid_amount;

                // Check if there are milestones
                $milestones = $escrow->milestones;
                if ($milestones->count() > 0) {
                    // Milestones pending approval notifications are now shown in top bar notifications
                    // (removed from dashboard actions)

                    // Check for milestones ready for payment
                    $readyMilestones = $milestones->filter(function($m) {
                        return $m->approval_status === 'approved' && $m->payment_status == Status::MILESTONE_UNFUNDED;
                    });
                    if ($readyMilestones->count() > 0) {
                        $totalDue = $readyMilestones->sum('amount');
                        $actions[] = [
                            'type' => 'milestones_ready_payment',
                            'message' => 'You have ' . $readyMilestones->count() . ' milestone(s) ready for payment. Total: ' . showAmount($totalDue),
                            'link' => route('user.escrow.milestone.index', $escrow->id),
                            'linkText' => 'Pay Milestones',
                            'priority' => 'high',
                            'escrow_id' => $escrow->id,
                            'listing_title' => $escrow->listing ? ($escrow->listing->title ?? $escrow->listing->domain_name ?? 'N/A') : 'N/A',
                        ];
                    }
                } elseif ($remainingAmount > 0) {
                    // No milestones, but buyer needs to pay the full amount
                    $actions[] = [
                        'type' => 'escrow_payment_required',
                        'message' => 'Please complete payment for your purchase. Remaining: ' . showAmount($remainingAmount),
                        'link' => route('user.escrow.details', $escrow->id),
                        'linkText' => 'Complete Payment',
                        'priority' => 'high',
                        'escrow_id' => $escrow->id,
                        'listing_title' => $escrow->listing ? ($escrow->listing->title ?? $escrow->listing->domain_name ?? 'N/A') : 'N/A',
                    ];
                }
            }

        }

        return $actions;
    }

}
