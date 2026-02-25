<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        $content = view('eden.settings.index')->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Settings',
            'sidebar' => 'admin',
            'activeNav' => 'settings',
            'dashboardLogo' => 'Eden Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }
}
