<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{

    public function withdrawMoney()
    {
        $withdrawMethod = WithdrawMethod::active()->get();
        $pageTitle = 'Withdraw Money';
        return view('Template::user.withdraw.methods', compact('pageTitle','withdrawMethod'));
    }

    public function withdrawStore(Request $request)
    {
        $request->validate([
            'method_code' => 'required',
            'amount' => 'required|numeric|min:0.01'
        ], [
            'method_code.required' => 'Please select a withdrawal method',
            'amount.required' => 'Please enter withdrawal amount',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Amount must be at least 0.01',
        ]);

        $method = WithdrawMethod::where('id', $request->method_code)->active()->firstOrFail();
        $user = auth()->user();
        
        // Validate amount limits with helpful messages
        if ($request->amount < $method->min_limit) {
            $notify[] = ['error', 'Minimum withdrawal amount is ' . showAmount($method->min_limit) . '. You entered ' . showAmount($request->amount)];
            return back()->withNotify($notify)->withInput($request->all());
        }
        
        if ($request->amount > $method->max_limit) {
            $notify[] = ['error', 'Maximum withdrawal amount is ' . showAmount($method->max_limit) . '. You entered ' . showAmount($request->amount)];
            return back()->withNotify($notify)->withInput($request->all());
        }

        // Calculate charges first to show user what they'll receive
        $charge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
        $afterCharge = $request->amount - $charge;

        if ($afterCharge <= 0) {
            // Calculate minimum needed to receive at least $1 after fees
            $minNeeded = ($method->fixed_charge + 1) / (1 - $method->percent_charge / 100);
            $notify[] = ['error', 'Withdrawal amount is too small. After fees (' . showAmount($charge) . '), you would receive nothing. Minimum amount needed: ' . showAmount(max($method->min_limit, $minNeeded))];
            return back()->withNotify($notify)->withInput($request->all());
        }

        // Show user what they'll receive before proceeding
        $notify[] = ['info', 'You will receive ' . showAmount($afterCharge * $method->rate) . ' ' . $method->currency . ' after fees (' . showAmount($charge) . ')'];

        // Check balance including charges
        if ($request->amount > $user->balance) {
            $notify[] = ['error', 'Insufficient balance. You have ' . showAmount($user->balance) . ' but need ' . showAmount($request->amount)];
            return back()->withNotify($notify)->withInput($request->all());
        }

        // Warn if withdrawing most of balance
        $balanceAfter = $user->balance - $request->amount;
        if ($balanceAfter < ($user->balance * 0.1) && $balanceAfter > 0) {
            $notify[] = ['warning', 'You are withdrawing most of your balance. Remaining balance will be ' . showAmount($balanceAfter)];
            // Don't block, just warn
        }

        $finalAmount = $afterCharge * $method->rate;

        $withdraw = new Withdrawal();
        $withdraw->method_id = $method->id; // wallet method ID
        $withdraw->user_id = $user->id;
        $withdraw->amount = $request->amount;
        $withdraw->currency = $method->currency;
        $withdraw->rate = $method->rate;
        $withdraw->charge = $charge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx = getTrx();
        $withdraw->save();

        // Log withdrawal initiation
        \Log::info('Withdrawal initiated', [
            'withdrawal_id' => $withdraw->id,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $request->amount,
            'charge' => $charge,
            'final_amount' => $finalAmount,
            'method' => $method->name,
            'currency' => $method->currency,
            'trx' => $withdraw->trx,
            'user_balance_before' => $user->balance,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        session()->put('wtrx', $withdraw->trx);
        return to_route('user.withdraw.preview');
    }

    public function withdrawPreview()
    {
        $trx = session()->get('wtrx');
        
        if (!$trx) {
            $notify[] = ['error', 'Session expired. Please start over.'];
            return redirect()->route('user.withdraw.money')->withNotify($notify);
        }

        $withdraw = Withdrawal::with('method','user')
            ->where('trx', $trx)
            ->where('status', Status::PAYMENT_INITIATE)
            ->orderBy('id','desc')
            ->firstOrFail();

        // Re-validate balance hasn't changed
        $user = auth()->user();
        if ($withdraw->amount > $user->balance) {
            $withdraw->delete(); // Remove invalid withdrawal
            session()->forget('wtrx');
            $notify[] = ['error', 'Your balance has changed. Please start over.'];
            return redirect()->route('user.withdraw.money')->withNotify($notify);
        }

        // Check if method is still active
        if ($withdraw->method->status == Status::DISABLE) {
            $withdraw->delete();
            session()->forget('wtrx');
            $notify[] = ['error', 'Withdrawal method is no longer available. Please select another method.'];
            return redirect()->route('user.withdraw.money')->withNotify($notify);
        }

        $pageTitle = 'Withdraw Preview';
        return view('Template::user.withdraw.preview', compact('pageTitle','withdraw'));
    }

    public function withdrawSubmit(Request $request)
    {
        $withdraw = Withdrawal::with('method','user')->where('trx', session()->get('wtrx'))->where('status', Status::PAYMENT_INITIATE)->orderBy('id','desc')->firstOrFail();

        $method = $withdraw->method;
        if ($method->status == Status::DISABLE) {
            abort(404);
        }

        $formData = @$method->form->form_data ?? [];

        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $user = auth()->user();
        if ($user->ts) {
            $response = verifyG2fa($user,$request->authenticator_code);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify)->withInput($request->all());
            }
        }

        // Final balance check before processing
        $user->refresh(); // Get latest balance
        if ($withdraw->amount > $user->balance) {
            $notify[] = ['error', 'Insufficient balance. Your balance is ' . showAmount($user->balance) . ' but you requested ' . showAmount($withdraw->amount)];
            return back()->withNotify($notify)->withInput($request->all());
        }

        // Check if method is still active
        if ($method->status == Status::DISABLE) {
            $notify[] = ['error', 'This withdrawal method is no longer available'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->withdraw_information = $userData;
        $withdraw->save();
        $user->balance  -=  $withdraw->amount;
        $user->save();

        $transaction = new Transaction();
        $transaction->user_id = $withdraw->user_id;
        $transaction->amount = $withdraw->amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge = $withdraw->charge;
        $transaction->trx_type = '-';
        $transaction->details = 'Withdraw request via ' . $withdraw->method->name;
        $transaction->trx = $withdraw->trx;
        $transaction->remark = 'withdraw';
        $transaction->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New withdraw request from '.$user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details',$withdraw->id);
        $adminNotification->save();

        notify($user, 'WITHDRAW_REQUEST', [
            'method_name' => $withdraw->method->name,
            'method_currency' => $withdraw->currency,
            'method_amount' => showAmount($withdraw->final_amount,currencyFormat:false),
            'amount' => showAmount($withdraw->amount,currencyFormat:false),
            'charge' => showAmount($withdraw->charge,currencyFormat:false),
            'rate' => showAmount($withdraw->rate,currencyFormat:false),
            'trx' => $withdraw->trx,
            'post_balance' => showAmount($user->balance,currencyFormat:false),
        ]);

        $notify[] = ['success', 'Withdraw request sent successfully'];
        return to_route('user.withdraw.history')->withNotify($notify);
    }

    public function withdrawLog(Request $request)
    {
        $pageTitle = "Withdrawal Log";
        $withdraws = Withdrawal::where('user_id', auth()->id())->where('status', '!=', Status::PAYMENT_INITIATE);
        if ($request->search) {
            $withdraws = $withdraws->where('trx',$request->search);
        }
        $withdraws = $withdraws->with('method')->orderBy('id','desc')->paginate(getPaginate());
        return view('Template::user.withdraw.log', compact('pageTitle','withdraws'));
    }
}
