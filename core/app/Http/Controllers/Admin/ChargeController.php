<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscrowCharge;
use App\Models\MarketplaceFee;
use App\Models\MarketplaceSetting;
use Illuminate\Http\Request;

class ChargeController extends Controller
{

    public function index()
    {
        $pageTitle = 'Escrow Charges';
        $charges   = EscrowCharge::all();
        $marketplaceSettings = MarketplaceSetting::getAllSettings();
        $marketplaceFees = MarketplaceFee::orderBy('context')->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.escrow.charges', compact('pageTitle', 'charges', 'marketplaceSettings', 'marketplaceFees'));
    }

    /**
     * Marketplace fee settings (moved from /admin/marketplace/config)
     */
    public function marketplaceFees(Request $request)
    {
        $request->validate([
            'listing_fee_percentage' => 'required|numeric|min:0|max:100',
            'escrow_fee_percentage' => 'required|numeric|min:0|max:50',
            'featured_listing_fee' => 'required|numeric|min:0|max:999999999',
        ]);

        MarketplaceSetting::setValue('listing_fee_percentage', $request->listing_fee_percentage);
        MarketplaceSetting::setValue('escrow_fee_percentage', $request->escrow_fee_percentage);
        MarketplaceSetting::setValue('featured_listing_fee', $request->featured_listing_fee);
        MarketplaceSetting::clearCache();

        $notify[] = ['success', 'Marketplace fee settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function globalCharge(Request $request)
    {
        $request->validate([
            'charge_cap'     => 'required|numeric|gte:0',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|gte:0',
        ]);

        $general                 = gs();
        $general->charge_cap     = $request->charge_cap;
        $general->fixed_charge   = $request->fixed_charge;
        $general->percent_charge = $request->percent_charge;
        $general->save();

        $notify[] = ['success', 'Global charge settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'minimum'        => 'required|numeric|gt:0',
            'maximum'        => 'required|numeric|gt:minimum',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|gte:0|regex:/^\d+(\.\d{1,2})?$/',
        ]);

        if ($id) {
            $charge  = EscrowCharge::findOrFail($id);
            $message = 'Charge updated successfully';
        } else {
            $charge  = new EscrowCharge();
            $message = 'Charge added successfully';
        }

        $charge->minimum        = $request->minimum;
        $charge->maximum        = $request->maximum;
        $charge->fixed_charge   = $request->fixed_charge;
        $charge->percent_charge = $request->percent_charge;
        $charge->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function remove($id)
    {
        $charge = EscrowCharge::findOrFail($id);
        $charge->delete();
        $notify[] = ['success', 'Charge deleted successfully'];
        return back()->withNotify($notify);
    }
}
