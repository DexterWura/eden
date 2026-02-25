<?php

use Illuminate\Support\Facades\Route;

$page = function (string $view, ?string $title = null, ?string $scripts = null) {
    return response()->view('eden.layout', [
        'title' => $title,
        'content' => view('eden.' . $view)->render(),
        'scripts' => $scripts ? view('eden.' . $scripts)->render() : '',
    ]);
};

Route::get('/', function () use ($page) {
    return $page('home', null, 'scripts-home');
});
Route::get('/about', fn () => $page('about', 'About'));
Route::get('/contact', fn () => $page('contact', 'Contact'));
Route::get('/submit', fn () => $page('submit', 'Submit your startup'));
Route::get('/categories', fn () => $page('categories', 'Categories'));
Route::get('/launching-today', function () use ($page) {
    return $page('launching-today', 'Launching today', 'scripts-launching-today');
});
Route::get('/startup', function () use ($page) {
    return $page('startup', 'Nexus Pay', 'scripts-startup');
});

Route::get('/founder-dashboard', function () {
    return response()->view('eden.layout-dashboard', [
        'title' => 'Founder dashboard',
        'sidebar' => 'founder',
        'dashboardLogo' => 'Eden',
        'dashboardTopbar' => '<button type="button" class="dash-account" title="Switch account">Nexus Pay · Founder</button>',
        'searchPlaceholder' => "Try searching 'upvotes this week'",
        'avatarTitle' => 'Account',
        'avatarLetter' => 'S',
        'content' => view('eden.founder-dashboard')->render(),
    ]);
});
Route::get('/admin-dashboard', function () {
    return response()->view('eden.layout-dashboard', [
        'title' => 'Admin dashboard',
        'sidebar' => 'admin',
        'dashboardLogo' => 'Eden Admin',
        'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All startups</button>',
        'searchPlaceholder' => "Try searching 'startups by category'",
        'avatarTitle' => 'Admin',
        'avatarLetter' => 'A',
        'content' => view('eden.admin-dashboard')->render(),
    ]);
});
