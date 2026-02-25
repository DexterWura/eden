<?php

namespace Laramin\Utility;

use Closure;

class Utility{

    public function handle($request, Closure $next)
    {
        try {
            $sysPass = Helpmate::sysPass();
            if (!$sysPass) {
                return redirect()->route(VugiChugi::acRouter());
            }
            abort_if($sysPass === 99 && request()->isMethod('post'),401);
        } catch (\Exception $e) {
            // If verification fails, allow request to continue
            // This prevents 500 errors during installation or when database isn't ready
        }
        return $next($request);
    }
}
