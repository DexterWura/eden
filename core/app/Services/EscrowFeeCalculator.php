<?php

namespace App\Services;

use App\Models\EscrowCharge;
use App\Models\MarketplaceFee;

/**
 * Centralized escrow/platform fee calculation.
 *
 * Mirrors the legacy logic in User\EscrowController::getCharge() and the
 * marketplace escrow creators (bids/offers/auction-end), including tiered
 * EscrowCharge overrides and charge caps.
 */
class EscrowFeeCalculator
{
    /**
     * Legacy single-fee calculation based on global settings + optional tier override.
     * Use calculateMarketplaceFees() for the new named-fee system.
     */
    public function calculate(float $amount): float
    {
        $general = gs();

        $percentCharge = (float)($general->percent_charge ?? 0);
        $fixedCharge = (float)($general->fixed_charge ?? 0);
        $chargeCap = (float)($general->charge_cap ?? 0);

        $escrowCharge = EscrowCharge::where('minimum', '<=', $amount)
            ->where('maximum', '>=', $amount)
            ->first();

        if ($escrowCharge) {
            $percentCharge = (float)($escrowCharge->percent_charge ?? $percentCharge);
            $fixedCharge = (float)($escrowCharge->fixed_charge ?? $fixedCharge);
        }

        $charge = ($amount * $percentCharge / 100) + $fixedCharge;

        if ($chargeCap > 0 && $charge > $chargeCap) {
            $charge = $chargeCap;
        }

        return (float) max(0, $charge);
    }

    /**
     * New fee system: sum enabled MarketplaceFee rows for a context and return a payer breakdown.
     *
     * If no MarketplaceFee rows exist for the context, falls back to legacy settings:
     * - escrow_service_fee: buyer pays the legacy fee
     * - direct_payout_listing_fee: seller pays the legacy fee
     *
     * @return array{
     *   used: string,
     *   total: float,
     *   buyer: float,
     *   seller: float,
     *   fees: array<int, array{name:string,context:string,payer:string,effective_payer:string,amount:float}>
     * }
     */
    public function calculateMarketplaceFees(float $amount, string $context, string $paymentMode = 'system'): array
    {
        // Direct payout: buyer should NOT be charged any escrow service fee.
        // The platform may still charge the seller a separate "direct payout listing fee" upstream.
        if ($context === 'escrow_service_fee' && $paymentMode === 'direct') {
            return [
                'used' => 'direct_zero',
                'total' => 0.0,
                'buyer' => 0.0,
                'seller' => 0.0,
                'fees' => [],
            ];
        }

        $fees = MarketplaceFee::where('context', $context)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $buyer = 0.0;
        $seller = 0.0;
        $items = [];

        if ($fees->count() === 0) {
            $legacy = $this->calculate($amount);
            $defaultPayer = $context === 'direct_payout_listing_fee' ? 'seller' : 'buyer';
            if ($defaultPayer === 'buyer') {
                $buyer = $legacy;
            } else {
                $seller = $legacy;
            }

            return [
                'used' => 'legacy',
                'total' => (float) ($buyer + $seller),
                'buyer' => (float) $buyer,
                'seller' => (float) $seller,
                'fees' => [
                    [
                        'name' => 'Legacy fee',
                        'context' => $context,
                        'payer' => $defaultPayer,
                        'effective_payer' => $defaultPayer,
                        'amount' => (float) ($buyer + $seller),
                    ],
                ],
            ];
        }

        foreach ($fees as $fee) {
            $percent = (float) ($fee->percent ?? 0);
            $fixed = (float) ($fee->fixed ?? 0);
            $cap = (float) ($fee->cap ?? 0);
            $calc = ($amount * $percent / 100) + $fixed;
            if ($cap > 0 && $calc > $cap) {
                $calc = $cap;
            }
            $calc = (float) max(0, $calc);

            $effectivePayer = (string) ($fee->payer ?? 'buyer');

            // Safety: in direct-payment escrows, the platform cannot reliably charge the seller at checkout.
            // Any seller-payer escrow service fees will be treated as buyer-paid.
            if ($context === 'escrow_service_fee' && $paymentMode === 'direct' && $effectivePayer === 'seller') {
                $effectivePayer = 'buyer';
            }

            if ($effectivePayer === 'seller') {
                $seller += $calc;
            } else {
                $buyer += $calc;
            }

            $items[] = [
                'name' => (string) $fee->name,
                'context' => (string) $fee->context,
                'payer' => (string) $fee->payer,
                'effective_payer' => $effectivePayer,
                'amount' => $calc,
            ];
        }

        return [
            'used' => 'marketplace_fees',
            'total' => (float) ($buyer + $seller),
            'buyer' => (float) $buyer,
            'seller' => (float) $seller,
            'fees' => $items,
        ];
    }
}


