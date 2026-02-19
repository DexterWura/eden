<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Startup;

class SubmissionController extends Controller
{
    public function index()
    {
        $submissions = Startup::whereNull('approved_at')->whereNotNull('submitted_at')->latest('submitted_at')->paginate(20);
        return view('admin.submissions.index', compact('submissions'));
    }
}
