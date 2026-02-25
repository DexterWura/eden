<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\Extension;
use Illuminate\Database\QueryException;

class Captcha
{
    /**
     * Run a callable that may query extensions table; return default if table missing.
     */
    private static function whenExtensionsExist(callable $fn, mixed $default = null): mixed
    {
        try {
            return $fn();
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            if ($e->getCode() === '42S02' || str_contains($msg, "doesn't exist") || str_contains($msg, 'Base table or view not found')) {
                return $default;
            }
            throw $e;
        }
    }

    /**
     * Google recaptcha2 script
     */
    public static function reCaptcha(): ?string
    {
        return self::whenExtensionsExist(function () {
            $reCaptcha = Extension::where('act', 'google-recaptcha2')->where('status', Status::ENABLE)->first();
            return $reCaptcha ? $reCaptcha->generateScript() : null;
        }, null);
    }

    /**
     * Custom captcha script (returns 0 when disabled or extensions table missing).
     */
    public static function customCaptcha($width = '100%', $height = 46, $bgColor = '#003')
    {
        return self::whenExtensionsExist(function () use ($width, $height, $bgColor) {
            $textColor = '#' . gs('base_color');
            $captcha = Extension::where('act', 'custom-captcha')->where('status', Status::ENABLE)->first();
            if (!$captcha) {
                return 0;
            }
            $code = rand(100000, 999999);
            $char = str_split($code);
            $ret = '<link href="https://fonts.googleapis.com/css?family=Henny+Penny&display=swap" rel="stylesheet">';
            $ret .= '<div style="height: ' . $height . 'px; line-height: ' . $height . 'px; width:' . $width . '; text-align: center; background-color: ' . $bgColor . '; color: ' . $textColor . '; font-size: ' . ($height - 20) . 'px; font-weight: bold; letter-spacing: 20px; font-family: \'Henny Penny\', cursive;  -webkit-user-select: none; -moz-user-select: none;-ms-user-select: none;user-select: none;  display: flex; justify-content: center;">';
            foreach ($char as $value) {
                $ret .= '<span style="    float:left;     -webkit-transform: rotate(' . rand(-60, 60) . 'deg);">' . $value . '</span>';
            }
            $ret .= '</div>';
            $captchaSecret = hash_hmac('sha256', (string) $code, $captcha->shortcode->random_key->value);
            $ret .= '<input type="hidden" name="captcha_secret" value="' . $captchaSecret . '">';
            return $ret;
        }, 0);
    }

    /**
     * Verify all captcha (passes when no captcha is configured or extensions table missing).
     */
    public static function verify(): bool
    {
        $gCaptchaPass = self::verifyGoogleCaptcha();
        $cCaptchaPass = self::verifyCustomCaptcha();
        return $gCaptchaPass && $cCaptchaPass;
    }

    /**
     * Verify google recaptcha2
     */
    public static function verifyGoogleCaptcha(): bool
    {
        return self::whenExtensionsExist(function () {
            $googleCaptcha = Extension::where('act', 'google-recaptcha2')->where('status', Status::ENABLE)->first();
            if (!$googleCaptcha) {
                return true;
            }
            $resp = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $googleCaptcha->shortcode->secret_key->value . "&response=" . request()->input('g-recaptcha-response', '') . "&remoteip=" . getRealIP()), true);
            return !empty($resp['success']);
        }, true);
    }

    /**
     * Verify custom captcha (passes when no custom captcha configured or extensions table missing).
     */
    public static function verifyCustomCaptcha(): bool
    {
        return self::whenExtensionsExist(function () {
            $customCaptcha = Extension::where('act', 'custom-captcha')->where('status', Status::ENABLE)->first();
            if (!$customCaptcha) {
                return true;
            }
            $captchaSecret = hash_hmac('sha256', (string) request()->input('captcha', ''), $customCaptcha->shortcode->random_key->value);
            return hash_equals($captchaSecret, (string) request()->input('captcha_secret', ''));
        }, true);
    }
}
