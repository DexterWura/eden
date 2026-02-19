<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /install',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: ' . $base . '/sitemap.xml',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
