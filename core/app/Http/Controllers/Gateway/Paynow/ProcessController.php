<?php

namespace App\Http\Controllers\Gateway\Paynow;

use App\Constants\Status;
use App\Models\Deposit;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController;
use Illuminate\Http\Request;

// Include Paynow SDK
require_once __DIR__ . '/autoloader.php';

use Paynow\Payments\Paynow as PaynowSDK;

class ProcessController extends Controller
{
    /*
     * Paynow Gateway
     */

    public static function process($deposit)
    {
        $paynowAcc = json_decode($deposit->gatewayCurrency()->gateway_parameter);

        $integrationId = $paynowAcc->integration_id;
        $integrationKey = $paynowAcc->integration_key;
        
        // Return URL - where user returns after payment
        $returnUrl = url('ipn/paynow') . '?paynow-return=true&trx=' . $deposit->trx;
        
        // Result URL - callback URL for status updates
        $resultUrl = url('ipn/paynow');

        // Initialize Paynow SDK
        $paynow = new PaynowSDK(
            $integrationId,
            $integrationKey,
            $returnUrl,
            $resultUrl
        );

        // Create payment - first param is reference, second is auth email (optional but recommended)
        $payment = $paynow->createPayment($deposit->trx, auth()->user()->email);
        
        // Add payment item - first param is item name, second is amount
        $payment->add('Deposit Payment', $deposit->final_amount);
        
        // Set description
        $payment->setDescription('Deposit payment for transaction: ' . $deposit->trx);

        // Send payment to Paynow
        try {
            $response = $paynow->send($payment);
            
            if (!$response->success()) {
                $notify[] = ['error', 'Payment initiation failed: ' . $response->error()];
                return json_encode(['error' => true, 'message' => $response->error()]);
            }

            // Store poll URL in deposit for status checking
            $deposit->btc_wallet = $response->pollUrl();
            $deposit->save();

            $send['redirect'] = true;
            $send['redirect_url'] = $response->redirectUrl();
            
            return json_encode($send);
            
        } catch (\Exception $e) {
            $notify[] = ['error', 'Payment initiation failed: ' . $e->getMessage()];
            return json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    public function ipn(Request $request)
    {
        $track = $request->get('trx') ?? $request->get('reference');
        
        if (!$track) {
            $notify[] = ['error', 'Transaction reference not found'];
            return redirect()->route('user.deposit.history')->withNotify($notify);
        }

        $deposit = Deposit::where('trx', $track)->orderBy('id', 'DESC')->first();
        
        if (!$deposit) {
            $notify[] = ['error', 'Deposit not found'];
            return redirect()->route('user.deposit.history')->withNotify($notify);
        }

        $paynowAcc = json_decode($deposit->gatewayCurrency()->gateway_parameter);
        
        $integrationId = $paynowAcc->integration_id;
        $integrationKey = $paynowAcc->integration_key;
        
        // Return URL
        $returnUrl = url('ipn/paynow') . '?paynow-return=true&trx=' . $deposit->trx;
        
        // Result URL
        $resultUrl = url('ipn/paynow');

        // Initialize Paynow SDK
        $paynow = new PaynowSDK(
            $integrationId,
            $integrationKey,
            $returnUrl,
            $resultUrl
        );

        // Check if this is a return from Paynow (user clicked back)
        if ($request->get('paynow-return') == 'true') {
            // User returned from Paynow, check status using poll URL
            if ($deposit->btc_wallet) {
                try {
                    // Poll the transaction status
                    $status = $paynow->pollTransaction($deposit->btc_wallet);
                    
                    if ($status->paid()) {
                        // Payment was successful
                        if ($deposit->status == Status::PAYMENT_INITIATE) {
                            // Verify amount matches
                            if (abs($status->amount() - $deposit->final_amount) < 0.01) {
                                PaymentController::userDataUpdate($deposit);
                                
                                $deposit->detail = [
                                    'paynow_reference' => $status->paynowReference(),
                                    'reference' => $status->reference(),
                                    'status' => $status->status(),
                                    'amount' => $status->amount(),
                                ];
                                $deposit->save();
                                
                                $notify[] = ['success', 'Payment captured successfully'];
                                return redirect($deposit->success_url)->withNotify($notify);
                            } else {
                                $notify[] = ['error', 'Amount mismatch. Please contact support.'];
                            }
                        } else {
                            $notify[] = ['success', 'Payment already processed'];
                            return redirect($deposit->success_url)->withNotify($notify);
                        }
                    } else {
                        $notify[] = ['error', 'Payment not completed'];
                    }
                } catch (\Exception $e) {
                    $notify[] = ['error', 'Error checking payment status: ' . $e->getMessage()];
                }
            }
            
            // If we get here, payment wasn't successful or there was an error
            $notify[] = ['error', 'Payment was not successful'];
            return redirect($deposit->failed_url)->withNotify($notify);
        }

        // This is a status update callback from Paynow
        try {
            $status = $paynow->processStatusUpdate();
            
            if ($status->paid()) {
                // Payment was successful
                if ($deposit->status == Status::PAYMENT_INITIATE) {
                    // Verify amount matches
                    if (abs($status->amount() - $deposit->final_amount) < 0.01) {
                        PaymentController::userDataUpdate($deposit);
                        
                        $deposit->detail = [
                            'paynow_reference' => $status->paynowReference(),
                            'reference' => $status->reference(),
                            'status' => $status->status(),
                            'amount' => $status->amount(),
                        ];
                        $deposit->save();
                        
                        // Return success response to Paynow
                        return response('OK', 200);
                    } else {
                        return response('Amount mismatch', 400);
                    }
                } else {
                    // Already processed
                    return response('OK', 200);
                }
            } else {
                return response('Payment not completed', 400);
            }
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 400);
        }
    }
}

