<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Eden\AuthController;
use App\Http\Controllers\Eden\DashboardController;
use App\Http\Controllers\Eden\HomeController;
use App\Http\Controllers\Eden\PageController;
use App\Http\Controllers\Eden\StartupController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/admin', [AdminLoginController::class, 'showLoginForm'])->name('admin.login')->middleware('admin.guest');
Route::post('/admin', [AdminLoginController::class, 'login'])->middleware('admin.guest');
Route::get('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout')->middleware('admin');
Route::get('/admin/password/reset', fn () => redirect()->route('admin.login'))->name('admin.password.reset');

Route::redirect('/admin-dashboard', '/backoffice', 301);
Route::redirect('/founder-dashboard', '/founder', 301);
Route::redirect('/startup', '/founder', 301);

Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [PageController::class, 'about']);
Route::get('/contact', [PageController::class, 'contact']);
Route::get('/submit', [PageController::class, 'submit']);
Route::post('/submit', [PageController::class, 'submitStore']);
Route::get('/categories', [PageController::class, 'categories']);
Route::post('/subscribe', [PageController::class, 'subscribe']);
Route::get('/launching-today', [StartupController::class, 'launchingToday']);
Route::get('/startup/{slug}', [StartupController::class, 'show']);
Route::get('/founder', [DashboardController::class, 'founderDashboard'])->middleware('auth')->name('founder.dashboard');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('admin')->prefix('backoffice')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::get('migrations', [MigrationController::class, 'index'])->name('migration.index');
    Route::post('migrations/run', [MigrationController::class, 'run'])->name('migration.run');
    Route::post('migrations/refresh', [MigrationController::class, 'refresh'])->name('migration.refresh');
    Route::post('migrations/rollback', [MigrationController::class, 'rollback'])->name('migration.rollback');
    Route::post('migrations/run/{migration}', [MigrationController::class, 'runSpecific'])->name('migration.run.specific');
    Route::get('migrations/download-sql', [MigrationController::class, 'downloadSql'])->name('migration.download.sql');
    Route::post('cache/clear', function () {
        Artisan::call('cache:clear');
        return redirect()->back()->with('success', __('Application cache cleared.'));
    })->name('cache.clear');
});
