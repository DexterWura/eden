<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use Illuminate\Http\Request;

class PruningController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
        $query = Startup::query();
        if ($request->filled('url_pattern')) {
            $query->where('url', 'like', $request->url_pattern);
        }
        if ($request->boolean('empty_description')) {
            $query->where(function ($q) {
                $q->whereNull('description')->orWhere('description', '');
            });
        }
        $startups = $query->limit(100)->get();
        return view('admin.pruning.index', compact('startups'));
    }

    public function destroy(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:startups,id',
        ]);
        $ids = array_unique(array_filter($request->ids));
        if (empty($ids)) {
            return redirect()->route('admin.pruning.index')->with('error', 'Select at least one startup to delete.');
        }
        Startup::whereIn('id', $ids)->delete();
        return redirect()->route('admin.pruning.index')->with('success', count($ids) . ' startup(s) deleted.');
    }
}
