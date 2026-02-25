<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All Reviews';
        $reviews = $this->getReviews($request)->paginate(getPaginate());
        return view('admin.review.index', compact('pageTitle', 'reviews'));
    }

    public function pending(Request $request)
    {
        $pageTitle = 'Pending Reviews';
        $reviews = $this->getReviews($request)->where('status', Status::REVIEW_PENDING)->paginate(getPaginate());
        return view('admin.review.index', compact('pageTitle', 'reviews'));
    }

    public function details($id)
    {
        $pageTitle = 'Review Details';
        $review = Review::with(['listing', 'reviewer', 'reviewedUser', 'escrow'])->findOrFail($id);
        return view('admin.review.details', compact('pageTitle', 'review'));
    }

    public function approve($id)
    {
        $review = Review::where('status', Status::REVIEW_PENDING)->findOrFail($id);

        $review->status = Status::REVIEW_APPROVED;
        $review->save();

        // Update reviewed user's average rating
        $this->updateUserRating($review->reviewed_user_id);

        $notify[] = ['success', 'Review approved'];
        return back()->withNotify($notify);
    }

    public function hide($id)
    {
        $review = Review::findOrFail($id);

        $review->status = Status::REVIEW_HIDDEN;
        $review->save();

        // Update reviewed user's average rating
        $this->updateUserRating($review->reviewed_user_id);

        $notify[] = ['success', 'Review hidden'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $review = Review::findOrFail($id);
        $userId = $review->reviewed_user_id;

        $review->delete();

        // Update reviewed user's average rating
        $this->updateUserRating($userId);

        $notify[] = ['success', 'Review deleted'];
        return back()->withNotify($notify);
    }

    private function getReviews($request)
    {
        return Review::with(['listing', 'reviewer', 'reviewedUser'])
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('review', 'LIKE', "%{$search}%")
                        ->orWhereHas('reviewer', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('reviewedUser', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc');
    }

    private function updateUserRating($userId)
    {
        $reviews = Review::where('reviewed_user_id', $userId)
            ->where('status', Status::REVIEW_APPROVED)
            ->get();

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews > 0 ? $reviews->avg('overall_rating') : 0;

        \App\Models\User::where('id', $userId)->update([
            'avg_rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
        ]);
    }
}

