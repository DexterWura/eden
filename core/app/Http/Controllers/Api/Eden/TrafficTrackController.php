<?php

namespace App\Http\Controllers\Api\Eden;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use App\Models\StartupTrafficDaily;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrafficTrackController extends Controller
{
    /**
     * Serves the tracking script. Add to your site: <script async src=".../track.js?slug=your-startup-slug"></script>
     */
    public function script(Request $request): Response
    {
        $slug = $request->query('slug');
        if (! $slug || ! is_string($slug)) {
            return $this->emptyScript();
        }

        $startup = Startup::where('slug', trim($slug))
            ->where('traffic_tracking_enabled', true)
            ->where('status', Startup::STATUS_ACTIVE)
            ->first();
        if (! $startup) {
            return $this->emptyScript();
        }

        $baseUrl = rtrim(url('/'), '/');
        $trackUrl = $baseUrl . '/api/eden/v1/track?slug=' . rawurlencode($startup->slug);

        $js = <<<JS
(function(){
var u='{$trackUrl}';
if(document.readyState==='complete'){var i=new Image();i.src=u+'&t='+Date.now();}else{window.addEventListener('load',function(){var i=new Image();i.src=u+'&t='+Date.now();});}
})();
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Records a visit and returns a 1x1 transparent GIF.
     */
    public function track(Request $request): Response
    {
        $slug = $request->query('slug');
        if (! $slug || ! is_string($slug)) {
            return $this->gifResponse();
        }

        $startup = Startup::where('slug', trim($slug))
            ->where('traffic_tracking_enabled', true)
            ->where('status', Startup::STATUS_ACTIVE)
            ->first();
        if (! $startup) {
            return $this->gifResponse();
        }

        $date = now()->toDateString();
        $row = StartupTrafficDaily::firstOrCreate(
            ['startup_id' => $startup->id, 'date' => $date],
            ['visits' => 0]
        );
        $row->increment('visits');

        return $this->gifResponse();
    }

    private function emptyScript(): Response
    {
        return response('(function(){})();', 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    private function gifResponse(): Response
    {
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
