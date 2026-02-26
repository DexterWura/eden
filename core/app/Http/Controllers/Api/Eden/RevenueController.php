<?php

namespace App\Http\Controllers\Api\Eden;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\StartupRevenueEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    public function record(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3|alpha',
            'external_id' => 'nullable|string|max:255',
            'mrr' => 'nullable|numeric|min:0',
        ], [
            'amount.required' => 'amount is required and must be a non-negative number.',
            'currency.required' => 'currency is required (3-letter ISO code, e.g. USD).',
        ]);

        /** @var Startup $startup */
        $startup = $request->attributes->get('eden_revenue_startup');

        $externalId = isset($validated['external_id']) && $validated['external_id'] !== ''
            ? (string) $validated['external_id']
            : null;

        if ($externalId !== null) {
            $existing = StartupRevenueEvent::where('startup_id', $startup->id)
                ->where('external_id', $externalId)
                ->first();

            if ($existing !== null) {
                return response()->json([
                    'message' => 'Payment already recorded (idempotent).',
                    'external_id' => $externalId,
                    'revenue_total' => (float) $startup->fresh()->revenue,
                    'event_id' => $existing->id,
                ], 200, ['Content-Type' => 'application/json']);
            }
        }

        $amount = (float) $validated['amount'];
        $currency = strtoupper((string) $validated['currency']);

        $event = DB::transaction(function () use ($startup, $amount, $currency, $externalId, $validated) {
            $event = StartupRevenueEvent::create([
                'startup_id' => $startup->id,
                'amount' => $amount,
                'currency' => $currency,
                'external_id' => $externalId,
                'raw_payload' => array_intersect_key($validated, array_flip(['amount', 'currency', 'external_id', 'mrr'])),
            ]);

            $startup->increment('revenue', $amount);

            if (isset($validated['mrr']) && is_numeric($validated['mrr']) && $validated['mrr'] >= 0) {
                $startup->update(['mrr' => (float) $validated['mrr']]);
            }

            return $event;
        });

        $startup->refresh();

        return response()->json([
            'message' => 'Revenue recorded.',
            'event_id' => $event->id,
            'amount' => $amount,
            'currency' => $currency,
            'revenue_total' => (float) $startup->revenue,
        ], 201, ['Content-Type' => 'application/json']);
    }
}
