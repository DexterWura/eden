<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use Closure;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Facades\View;

class ActiveTemplateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $viewShare['activeTemplate']     = activeTemplate();
            $viewShare['activeTemplateTrue'] = activeTemplate(true);
            view()->share($viewShare);

            view()->composer([$viewShare['activeTemplate'] . "partials.header", $viewShare['activeTemplate'] . "partials.footer"], function ($view) {
                try {
                    $view->with([
                        'pages' => Page::where('is_default', Status::NO)->where('tempname', activeTemplate())->orderBy('id', 'DESC')->get()
                    ]);
                } catch (\Exception $e) {
                    $view->with(['pages' => collect()]);
                }
            });

            View::addNamespace('Template', resource_path('views/templates/' . activeTemplateName()));
        } catch (\Exception $e) {
            // If anything fails, just continue with defaults
            $viewShare['activeTemplate'] = 'templates.basic.';
            $viewShare['activeTemplateTrue'] = 'assets/templates/basic/';
            view()->share($viewShare);
        }

        return $next($request);
    }
}
