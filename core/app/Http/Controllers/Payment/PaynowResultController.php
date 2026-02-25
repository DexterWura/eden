<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\FeaturePayment;
use App\Services\PaynowService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaynowResultController extends Controller
{
    public function __invoke(Request $request, PaynowService $paynow): Response
    {
        $status = $paynow->processStatusUpdate();
        if (! $status) {
            return response('', Response::HTTP_BAD_REQUEST);
        }

        $reference = $request->input('reference') ?? $request->input('referencenumber') ?? $request->input('ref');
        if (! $reference) {
            return response('', Response::HTTP_BAD_REQUEST);
        }

        $payment = FeaturePayment::find($reference);
        if (! $payment || $payment->status === FeaturePayment::STATUS_PAID) {
            return response('OK', Response::HTTP_OK);
        }

        if ($status->paid()) {
            $payment->update([
                'status' => FeaturePayment::STATUS_PAID,
                'paid_at' => now(),
            ]);
        }

        return response('OK', Response::HTTP_OK);
    }
}
