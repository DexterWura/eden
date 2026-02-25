<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'type' => 'required|in:listing,user',
            'id' => 'required|integer',
        ]);
        $type = $request->type;
        $id = $request->id;
        if ($type === 'listing') {
            $subject = Listing::where('status', '!=', \App\Constants\Status::LISTING_DRAFT)->findOrFail($id);
            $subjectType = 'listing';
            $subjectName = $subject->title;
        } else {
            $subject = User::where('status', 1)->findOrFail($id);
            if ($subject->id == auth()->id()) {
                abort(403, __('You cannot report yourself.'));
            }
            $subjectType = 'user';
            $subjectName = $subject->username ?? $subject->email;
        }
        $pageTitle = __('Report :type', ['type' => $subjectType === 'listing' ? __('Listing') : __('User')]);
        $reasonOptions = Report::reasonOptions();
        return view('Template::user.report.create', compact('pageTitle', 'subject', 'subjectType', 'subjectName', 'reasonOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reportable_type' => 'required|in:listing,user',
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|in:' . implode(',', array_keys(Report::reasonOptions())),
            'details' => 'nullable|string|max:2000',
        ]);
        $type = $request->reportable_type;
        $id = (int) $request->reportable_id;
        if ($type === 'listing') {
            Listing::where('status', '!=', \App\Constants\Status::LISTING_DRAFT)->findOrFail($id);
            $reportableType = Listing::class;
        } else {
            $user = User::where('status', 1)->findOrFail($id);
            if ($user->id == auth()->id()) {
                return back()->withNotify([['error', __('You cannot report yourself.')]]);
            }
            $reportableType = User::class;
        }
        Report::create([
            'user_id' => auth()->id(),
            'reportable_type' => $reportableType,
            'reportable_id' => $id,
            'reason' => $request->reason,
            'details' => $request->details,
            'status' => Report::STATUS_PENDING,
        ]);
        return back()->withNotify([['success', __('Thank you. Your report has been submitted and will be reviewed by our team.')]]);
    }
}
