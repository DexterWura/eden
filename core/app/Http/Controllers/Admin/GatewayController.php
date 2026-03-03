<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\ProPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::orderBy('name')->get();
        $totalRevenue = ProPayment::where('status', 'paid')->sum('amount');
        $totalPayments = ProPayment::where('status', 'paid')->count();

        $content = view('eden.admin.gateways-index', compact('gateways', 'totalRevenue', 'totalPayments'))->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Payment gateways',
            'sidebar' => 'admin',
            'activeNav' => 'gateways',
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
        ]);
    }

    public function edit(PaymentGateway $gateway)
    {
        $content = view('eden.admin.gateways-form', compact('gateway'))->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Edit gateway — ' . $gateway->name,
            'sidebar' => 'admin',
            'activeNav' => 'gateways',
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
        ]);
    }

    public function update(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'parameters' => 'nullable|array',
            'parameters.*' => 'nullable|string|max:1000',
        ]);

        $gateway->update([
            'enabled' => (bool) ($validated['enabled'] ?? false),
            'parameters' => $validated['parameters'] ?? $gateway->parameters,
        ]);

        return redirect()->route('admin.gateways.index')->with('notify', [['success', $gateway->name . ' updated.']]);
    }

    public function seed(): RedirectResponse
    {
        $defaults = [
            ['name' => 'PayPal', 'alias' => 'paypal', 'parameters' => ['client_id' => '', 'secret' => '', 'mode' => 'sandbox']],
            ['name' => 'Paynow', 'alias' => 'paynow', 'parameters' => ['integration_id' => '', 'integration_key' => '']],
        ];

        foreach ($defaults as $gw) {
            PaymentGateway::firstOrCreate(
                ['alias' => $gw['alias']],
                ['name' => $gw['name'], 'enabled' => false, 'parameters' => $gw['parameters']]
            );
        }

        return redirect()->route('admin.gateways.index')->with('notify', [['success', 'Default gateways seeded.']]);
    }
}
