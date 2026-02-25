<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Eden\DashboardController;
use App\Http\Controllers\Eden\HomeController;
use App\Http\Controllers\Eden\PageController;
use App\Http\Controllers\Eden\StartupController;
use Illuminate\Support\Facades\Route;

Route::get('/admin', [LoginController::class, 'showLoginForm'])->name('admin.login')->middleware('admin.guest');
Route::post('/admin', [LoginController::class, 'login'])->middleware('admin.guest');
Route::get('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout')->middleware('admin');
Route::get('/admin/password/reset', fn () => redirect()->route('admin.login'))->name('admin.password.reset');

Route::redirect('/admin-dashboard', '/backoffice', 301);
Route::redirect('/founder-dashboard', '/startup', 301);

Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [PageController::class, 'about']);
Route::get('/contact', [PageController::class, 'contact']);
Route::get('/submit', [PageController::class, 'submit']);
Route::get('/categories', [PageController::class, 'categories']);
Route::get('/launching-today', [StartupController::class, 'launchingToday']);
Route::get('/startup/{slug}', [StartupController::class, 'show']);
Route::get('/startup', [DashboardController::class, 'founderDashboard']);
Route::get('/backoffice', [DashboardController::class, 'adminDashboard'])->middleware('admin')->name('admin.dashboard');
