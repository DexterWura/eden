<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\Escrow;
use App\Models\Milestone;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Models\Listing;
use App\Models\Bid;
use App\Models\Offer;
use App\Models\Review;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function dashboard()
    {
        $pageTitle = 'Dashboard';

            // User Info
        $widget['total_users']             = User::count();
        $widget['verified_users']          = User::active()->count();
        $widget['email_unverified_users']  = User::emailUnverified()->count();
        $widget['mobile_unverified_users'] = User::mobileUnverified()->count();


            // user Browsing, Country, Operating Log
        $userLoginData = UserLogin::where('created_at', '>=', Carbon::now()->subDays(30))->get(['browser', 'os', 'country']);

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_os_counter'] = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);


        // Marketplace Statistics (Primary Focus)
        $marketplace['total_listings'] = Listing::count();
        $marketplace['active_listings'] = Listing::where('status', Status::LISTING_ACTIVE)->count();
        $marketplace['pending_listings'] = Listing::where('status', Status::LISTING_PENDING)->count();
        $marketplace['sold_listings'] = Listing::where('status', Status::LISTING_SOLD)->count();
        // Exclude test users from sales value
        $marketplace['total_sales_value'] = Listing::where('status', Status::LISTING_SOLD)
            ->whereHas('user', function($q) { $q->where('is_test_user', false); })
            ->sum('final_price');
        $marketplace['total_bids'] = Bid::count();
        $marketplace['total_offers'] = Offer::count();
        $marketplace['active_auctions'] = Listing::where('sale_type', 'auction')
            ->where('status', Status::LISTING_ACTIVE)
            ->where('auction_end', '>', now())
            ->count();
        $marketplace['total_views'] = \App\Models\ListingView::count();
        
        // Marketplace revenue from escrow fees on sold listings - exclude test users
        $soldListingEscrowIds = Listing::where('status', Status::LISTING_SOLD)
            ->where('escrow_id', '>', 0)
            ->whereHas('user', function($q) { $q->where('is_test_user', false); })
            ->pluck('escrow_id');
        $marketplace['marketplace_revenue'] = Escrow::whereIn('id', $soldListingEscrowIds)->sum('charge');

        // Financial metrics (secondary) - exclude test users
        $deposit['total_deposit_amount']   = Deposit::successful()->whereHas('user', function($q) { $q->where('is_test_user', false); })->sum('amount');
        $deposit['total_deposit_pending']  = Deposit::pending()->whereHas('user', function($q) { $q->where('is_test_user', false); })->count();
        $deposit['total_deposit_rejected'] = Deposit::rejected()->whereHas('user', function($q) { $q->where('is_test_user', false); })->count();
        $deposit['total_deposit_charge']   = Deposit::successful()->whereHas('user', function($q) { $q->where('is_test_user', false); })->sum('charge');

        $withdrawals['total_withdraw_amount']   = Withdrawal::approved()->whereHas('user', function($q) { $q->where('is_test_user', false); })->sum('amount');
        $withdrawals['total_withdraw_pending']  = Withdrawal::pending()->whereHas('user', function($q) { $q->where('is_test_user', false); })->count();
        $withdrawals['total_withdraw_rejected'] = Withdrawal::rejected()->whereHas('user', function($q) { $q->where('is_test_user', false); })->count();
        $withdrawals['total_withdraw_charge']   = Withdrawal::approved()->whereHas('user', function($q) { $q->where('is_test_user', false); })->sum('charge');

        // Escrow metrics (tertiary - only for marketplace-related escrows) - exclude test users
        $marketplaceEscrowIds = Listing::where('escrow_id', '>', 0)
            ->whereHas('user', function($q) { $q->where('is_test_user', false); })
            ->pluck('escrow_id');
        $dataEscrow['total']     = Escrow::whereIn('id', $marketplaceEscrowIds)->sum('amount');
        $dataEscrow['disputed']  = Escrow::whereIn('id', $marketplaceEscrowIds)->disputed()->count();
        $dataEscrow['cancelled'] = Escrow::whereIn('id', $marketplaceEscrowIds)->canceled()->count();
        $dataEscrow['funded']    = Milestone::whereIn('escrow_id', $marketplaceEscrowIds)->funded()->sum('amount');

        // Marketplace counters for sidenav
        $pendingListingsCount = Listing::where('status', Status::LISTING_PENDING)->count();
        $pendingOffersCount = Offer::where('status', Status::OFFER_PENDING)->count();
        $pendingReviewsCount = Review::where('status', Status::REVIEW_PENDING)->count();

        return view('admin.dashboard', compact('pageTitle', 'widget', 'chart', 'deposit', 'withdrawals', 'dataEscrow', 'marketplace', 'pendingListingsCount', 'pendingOffersCount', 'pendingReviewsCount'));
    }




    public function depositAndWithdrawReport(Request $request)
    {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }
        $deposits = Deposit::successful()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();


        $withdrawals = Withdrawal::approved()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on'  => $date,
                'deposits'    => getAmount($deposits->where('created_on', $date)->first()?->amount ?? 0),
                'withdrawals' => getAmount($withdrawals->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

            // Monthly Deposit & Withdraw Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Deposited',
                'data' => $data->pluck('deposits')
            ],
            [
                'name' => 'Withdrawn',
                'data' => $data->pluck('withdrawals')
            ]
        ];

        return response()->json($report);
    }

    public function transactionReport(Request $request)
    {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $plusTransactions = Transaction::where('trx_type', '+')
            ->whereHas('user', function($q) { $q->where('is_test_user', false); })
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $minusTransactions = Transaction::where('trx_type', '-')
            ->whereHas('user', function($q) { $q->where('is_test_user', false); })
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();


        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'credits'    => getAmount($plusTransactions->where('created_on', $date)->first()?->amount ?? 0),
                'debits'     => getAmount($minusTransactions->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

            // Monthly Deposit & Withdraw Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Plus Transactions',
                'data' => $data->pluck('credits')
            ],
            [
                'name' => 'Minus Transactions',
                'data' => $data->pluck('debits')
            ]
        ];

        return response()->json($report);
    }


    private function getAllDates($startDate, $endDate)
    {
        $dates       = [];
        $currentDate = new \DateTime($startDate);
        $endDate     = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('d-F-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function  getAllMonths($startDate, $endDate)
    {
        if ($endDate > now()) {
            $endDate = now()->format('Y-m-d');
        }

        $startDate = new \DateTime($startDate);
        $endDate   = new \DateTime($endDate);

        $months = [];

        while ($startDate <= $endDate) {
            $months[] = $startDate->format('F-Y');
            $startDate->modify('+1 month');
        }

        return $months;
    }


    public function profile()
    {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password()
    {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|min:8|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications()
    {
        $notifications   = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $hasUnread       = AdminNotification::where('is_read', Status::NO)->exists();
        $hasNotification = AdminNotification::exists();
        $pageTitle       = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }


    public function notificationRead($id)
    {
        $notification          = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport()
    {
        $pageTitle            = 'Your Listed Report & Request';
        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $url                  = "https://license.viserlab.com/issue/get?" . http_build_query($arr);
        $response             = CurlRequest::curlContent($url);
        $response             = json_decode($response);
        if (!$response || !@$response->status || !@$response->message) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return to_route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request)
    {
        $request->validate([
            'type'    => 'required|in:bug,feature',
            'message' => 'required',
        ]);

        // Format the message for WhatsApp
        $admin = auth('admin')->user();
        $adminName = $admin ? $admin->name : 'Admin';
        $adminEmail = $admin ? $admin->email : 'N/A';
        $appName = systemDetails()['name'];
        $appUrl = env('APP_URL');
        
        $reportType = ucfirst($request->type);
        $whatsappMessage = "📋 *{$reportType} Report from {$appName}*\n\n";
        $whatsappMessage .= "👤 *Admin:* {$adminName}\n";
        $whatsappMessage .= "📧 *Email:* {$adminEmail}\n";
        $whatsappMessage .= "🌐 *Site:* {$appUrl}\n";
        $whatsappMessage .= "📝 *Type:* {$reportType}\n\n";
        $whatsappMessage .= "*Message:*\n{$request->message}";
        
        // Encode the message for URL
        $encodedMessage = urlencode($whatsappMessage);
        
        // WhatsApp number: 263787689820 (Zimbabwe format)
        $whatsappNumber = '263787689820';
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
        
        // Redirect to WhatsApp web/app with pre-filled message
        $notify[] = ['success', 'Opening WhatsApp to send your report...'];
        return redirect($whatsappUrl)->withNotify($notify);
    }

    public function readAllNotification()
    {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification()
    {
        AdminNotification::truncate();
        $notify[] = ['success', 'Notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id)
    {
        AdminNotification::where('id', $id)->delete();
        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }
}
