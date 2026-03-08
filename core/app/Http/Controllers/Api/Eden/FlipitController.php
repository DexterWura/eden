<?php

namespace App\Http\Controllers\Api\Eden;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlipitController extends Controller
{
    /**
     * Called by FLIPit when a listing is sold. Marks the linked startup as sold in Eden.
     * Future: new_owner_email, etc. can be used for transfer flows.
     */
    public function listingSold(Request $request): JsonResponse
    {
        $secret = config('services.flipit.webhook_secret');
        if ($secret !== null && $secret !== '') {
            $header = $request->header('X-Eden-Flipit-Secret');
            if (! is_string($header) || ! hash_equals($secret, $header)) {
                return response()->json([
                    'error' => 'unauthorized',
                    'message' => 'Invalid or missing webhook secret.',
                ], 401, ['Content-Type' => 'application/json']);
            }
        }

        $validated = $request->validate([
            'listing_id' => 'required|string|max:255',
            'new_owner_email' => 'nullable|string|email|max:255',
        ], [
            'listing_id.required' => 'listing_id is required.',
        ]);

        $listingId = trim((string) $validated['listing_id']);
        if (Startup::isFlipitListingNumber($listingId)) {
            $listingId = strtoupper($listingId);
        }
        $startup = Startup::where('flipit_listing_id', $listingId)->first();

        if ($startup === null) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'No startup linked to this FLIPit listing ID.',
            ], 404, ['Content-Type' => 'application/json']);
        }

        $startup->update([
            'for_sale' => false,
            'sold_at' => now(),
        ]);

        $payload = [
            'message' => 'Startup marked as sold.',
            'startup_id' => $startup->id,
            'listing_id' => $listingId,
        ];
        if (! empty($validated['new_owner_email'])) {
            $payload['new_owner_email'] = $validated['new_owner_email'];
            // Future: persist new owner and trigger transfer workflow
        }

        return response()->json($payload, 200, ['Content-Type' => 'application/json']);
    }
}
