<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceFee;
use Illuminate\Http\Request;

class MarketplaceFeeController extends Controller
{
    public function index()
    {
        $pageTitle = 'Marketplace Fees';
        $fees = MarketplaceFee::orderBy('context')->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.marketplace.fees', compact('pageTitle', 'fees'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'context' => 'required|in:escrow_service_fee,direct_payout_listing_fee',
            'payer' => 'required|in:buyer,seller',
            'percent' => 'required|numeric|gte:0|max:100',
            'fixed' => 'required|numeric|gte:0',
            'cap' => 'nullable|numeric|gte:0',
            'sort_order' => 'nullable|integer|min:0|max:100000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->context === 'direct_payout_listing_fee' && $request->payer !== 'seller') {
            return back()->withNotify([['error', 'Direct payout listing fees must be paid by the seller.']]);
        }

        if ((float)$request->percent <= 0 && (float)$request->fixed <= 0) {
            return back()->withNotify([['error', 'Fee must have either a percent or a fixed amount (or both).']]);
        }

        $fee = $id ? MarketplaceFee::findOrFail($id) : new MarketplaceFee();
        $fee->name = $request->name;
        $fee->context = $request->context;
        $fee->payer = $request->payer;
        $fee->percent = $request->percent;
        $fee->fixed = $request->fixed;
        $fee->cap = $request->cap;
        $fee->sort_order = $request->sort_order ?? 0;
        $fee->is_active = $request->has('is_active') ? (bool)$request->is_active : true;
        $fee->save();

        $notify[] = ['success', $id ? 'Marketplace fee updated successfully' : 'Marketplace fee created successfully'];
        if ($fee->context === 'escrow_service_fee' && $fee->payer === 'seller') {
            $notify[] = ['warning', 'Note: For Direct-payment escrows, seller-paid escrow service fees are automatically treated as buyer-paid.'];
        }
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $fee = MarketplaceFee::findOrFail($id);
        $fee->is_active = !$fee->is_active;
        $fee->save();

        $notify[] = ['success', 'Marketplace fee status updated'];
        return back()->withNotify($notify);
    }

    public function remove($id)
    {
        $fee = MarketplaceFee::findOrFail($id);
        $fee->delete();

        $notify[] = ['success', 'Marketplace fee deleted successfully'];
        return back()->withNotify($notify);
    }
}


