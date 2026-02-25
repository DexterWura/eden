<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function create($escrowId)
    {
        $escrow = Escrow::where('id', $escrowId)
            ->where('buyer_id', auth()->id())
            ->where('status', Status::ESCROW_COMPLETED)
            ->with(['listing', 'seller'])
            ->firstOrFail();

        $existingReview = Review::where('escrow_id', $escrow->id)
            ->where('reviewer_id', auth()->id())
            ->where('review_type', 'buyer_review')
            ->first();

        if ($existingReview) {
            return redirect()->route('user.escrow.details', $escrow->id)
                ->withNotify([['info', 'You have already left a review for this transaction.']]);
        }

        $pageTitle = 'Leave a Review';
        return view('Template::user.review.create', compact('pageTitle', 'escrow'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'escrow_id' => 'required|integer|exists:escrows,id',
            'overall_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'accuracy_rating' => 'nullable|integer|min:1|max:5',
            'timeliness_rating' => 'nullable|integer|min:1|max:5',
            'review' => 'required|string|max:2000',
        ]);

        $escrow = Escrow::where('id', $request->escrow_id)
            ->where('buyer_id', auth()->id())
            ->where('status', Status::ESCROW_COMPLETED)
            ->with('listing')
            ->firstOrFail();

        $existingReview = Review::where('escrow_id', $escrow->id)
            ->where('reviewer_id', auth()->id())
            ->where('review_type', 'buyer_review')
            ->first();

        if ($existingReview) {
            return back()->withNotify([['error', 'You have already left a review for this transaction.']]);
        }

        Review::create([
            'listing_id' => $escrow->listing_id,
            'escrow_id' => $escrow->id,
            'reviewer_id' => auth()->id(),
            'reviewed_user_id' => $escrow->seller_id,
            'review_type' => 'buyer_review',
            'overall_rating' => $request->overall_rating,
            'communication_rating' => $request->communication_rating,
            'accuracy_rating' => $request->accuracy_rating,
            'timeliness_rating' => $request->timeliness_rating,
            'review' => $request->review,
            'status' => Status::REVIEW_PENDING,
        ]);

        return redirect()->route('user.escrow.details', $escrow->id)
            ->withNotify([['success', 'Thank you! Your review has been submitted and will be visible after moderation.']]);
    }
}
