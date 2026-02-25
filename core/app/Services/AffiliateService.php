<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateService
{
    /** Referral session expires after this many days (0 = no expiry). */
    public const REFERRAL_SESSION_DAYS = 30;

    /**
     * Get referrer username from session if set and not expired. Clears session keys if expired.
     */
    public static function getReferrerUsernameFromSession(): ?string
    {
        $referBy = session()->get('reference');
        if (!$referBy) {
            return null;
        }
        $at = session()->get('reference_at');
        if ($at && self::REFERRAL_SESSION_DAYS > 0) {
            $expiry = (int) self::REFERRAL_SESSION_DAYS * 86400;
            if (time() - (int) $at > $expiry) {
                session()->forget(['reference', 'reference_at']);
                return null;
            }
        }
        return $referBy;
    }

    /**
     * Store referrer username in session and set timestamp for expiry.
     */
    public static function setReferrerInSession(string $username): void
    {
        session()->put('reference', $username);
        session()->put('reference_at', now()->timestamp);
    }
    /**
     * Credit the referrer for a new user signup when affiliate is enabled.
     * Skips when disabled, amount is 0, no referrer, or self-referral (same user or same email/phone).
     */
    public static function creditReferrer(User $newUser): bool
    {
        if (!$newUser->ref_by) {
            return false;
        }

        $referrer = User::find($newUser->ref_by);
        if (!$referrer) {
            return false;
        }

        if (self::isSelfReferral($newUser, $referrer)) {
            $newUser->ref_by = 0;
            $newUser->save();
            return false;
        }

        $affiliateEnable = (int) (gs('affiliate_enable') ?? 0);
        $affiliateSignupAmount = (float) (gs('affiliate_signup_amount') ?? 0);
        if (!$affiliateEnable || $affiliateSignupAmount <= 0) {
            return false;
        }

        try {
            DB::transaction(function () use ($referrer, $affiliateSignupAmount, $newUser) {
                $referrer = User::where('id', $referrer->id)->lockForUpdate()->first();
                if (!$referrer) {
                    return;
                }
                $referrer->balance += $affiliateSignupAmount;
                $referrer->save();

                $trx = getTrx();
                $transaction = new Transaction();
                $transaction->user_id = $referrer->id;
                $transaction->amount = $affiliateSignupAmount;
                $transaction->post_balance = $referrer->balance;
                $transaction->charge = 0;
                $transaction->trx_type = '+';
                $transaction->remark = 'affiliate_signup';
                $transaction->details = 'Referral signup bonus for ' . $newUser->username;
                $transaction->trx = $trx;
                $transaction->save();
            });
            return true;
        } catch (\Exception $e) {
            \Log::warning('Affiliate signup credit failed: ' . $e->getMessage(), [
                'referrer_id' => $referrer->id,
                'new_user_id' => $newUser->id,
            ]);
            return false;
        }
    }

    /**
     * Self-referral: same user id, or same email, or same mobile (same person).
     */
    protected static function isSelfReferral(User $newUser, User $referrer): bool
    {
        if ($referrer->id === $newUser->id) {
            return true;
        }
        if (!empty($newUser->email) && !empty($referrer->email) && strcasecmp(trim($newUser->email), trim($referrer->email)) === 0) {
            return true;
        }
        if (!empty($newUser->mobile) && !empty($referrer->mobile) && trim($newUser->mobile) === trim($referrer->mobile)) {
            return true;
        }
        return false;
    }
}
