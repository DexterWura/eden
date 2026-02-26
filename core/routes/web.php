<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StartupController as AdminStartupController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Eden\AuthController;
use App\Http\Controllers\Eden\ClaimController;
use App\Http\Controllers\Eden\DashboardController;
use App\Http\Controllers\Eden\HomeController;
use App\Http\Controllers\Eden\PageController;
use App\Http\Controllers\Eden\StartupController;
use App\Http\Controllers\User\Auth\SocialiteController;
use App\Http\Controllers\Founder\SettingsController as FounderSettingsController;
use App\Http\Controllers\Founder\StartupController as FounderStartupController;
use App\Http\Controllers\Founder\UpvotesController;
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
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/about', [PageController::class, 'about']);
Route::get('/contact', [PageController::class, 'contact']);
Route::post('/contact', [PageController::class, 'contactStore']);
Route::get('/submit', [PageController::class, 'submit']);
Route::post('/submit', [PageController::class, 'submitStore']);
Route::get('/categories', [PageController::class, 'categories']);
Route::post('/subscribe', [PageController::class, 'subscribe']);
Route::get('/launching-today', [StartupController::class, 'launchingToday']);
Route::get('/startup/{slug}', [StartupController::class, 'show']);
Route::get('/startup/{slug}/out', [StartupController::class, 'out']);
Route::post('/startup/{slug}/upvote', [StartupController::class, 'upvote'])->name('startup.upvote')->middleware('auth');
Route::get('/startup/{slug}/claim', [ClaimController::class, 'show'])->name('startup.claim');
Route::post('/startup/{slug}/claim/confirm', [ClaimController::class, 'confirm'])->name('startup.claim.confirm')->middleware('auth');
Route::post('/startup/{slug}/claim/start', [ClaimController::class, 'startVerification'])->name('startup.claim.start')->middleware('auth');
Route::post('/startup/{slug}/claim/verify', [ClaimController::class, 'verify'])->name('startup.claim.verify')->middleware('auth');
Route::middleware('auth')->prefix('founder')->name('founder.')->group(function () {
    Route::get('/', [DashboardController::class, 'founderDashboard'])->name('dashboard');
    Route::get('startups', [FounderStartupController::class, 'index'])->name('startups.index');
    Route::get('startups/create', [FounderStartupController::class, 'create'])->name('startups.create');
    Route::post('startups', [FounderStartupController::class, 'store'])->name('startups.store');
    Route::get('startups/{startup}/edit', [FounderStartupController::class, 'edit'])->name('startups.edit');
    Route::put('startups/{startup}', [FounderStartupController::class, 'update'])->name('startups.update');
    Route::get('upvotes', [UpvotesController::class, 'index'])->name('upvotes');
    Route::get('settings', [FounderSettingsController::class, 'index'])->name('settings');
    Route::put('settings', [FounderSettingsController::class, 'update'])->name('settings.update');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/social/{provider}', [SocialiteController::class, 'socialLogin'])->name('user.social.login')->where('provider', 'google|facebook|linkedin|twitter');
Route::get('/auth/social/callback/{provider}', [SocialiteController::class, 'callback'])->name('user.social.login.callback')->where('provider', 'google|facebook|linkedin|twitter');

Route::middleware('admin')->prefix('backoffice')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::get('migrations', [MigrationController::class, 'index'])->name('migration.index');
    Route::get('startups', [AdminStartupController::class, 'index'])->name('startups.index');
    Route::get('startups/create', [AdminStartupController::class, 'create'])->name('startups.create');
    Route::post('startups', [AdminStartupController::class, 'store'])->name('startups.store');
    Route::get('startups/{startup}/edit', [AdminStartupController::class, 'edit'])->name('startups.edit');
    Route::put('startups/{startup}', [AdminStartupController::class, 'update'])->name('startups.update');
    Route::post('startups/{startup}/disable', [AdminStartupController::class, 'disable'])->name('startups.disable');
    Route::post('startups/{startup}/activate', [AdminStartupController::class, 'activate'])->name('startups.activate');
    Route::post('startups/{startup}/ban', [AdminStartupController::class, 'ban'])->name('startups.ban');
    Route::post('startups/{startup}/unban', [AdminStartupController::class, 'unban'])->name('startups.unban');
    Route::post('startups/{startup}/featured', [AdminStartupController::class, 'toggleFeatured'])->name('startups.toggle-featured');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    Route::get('subscribers/import', [SubscriberController::class, 'import'])->name('subscribers.import');
    Route::post('subscribers/import', [SubscriberController::class, 'importStore'])->name('subscribers.import.store');
    Route::get('subscribers/compose', [SubscriberController::class, 'compose'])->name('subscribers.compose');
    Route::post('subscribers/preview', [SubscriberController::class, 'preview'])->name('subscribers.preview');
    Route::post('subscribers/send', [SubscriberController::class, 'send'])->name('subscribers.send');
    Route::delete('subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings/seo', [SettingsController::class, 'updateSeo'])->name('settings.seo');
    Route::post('settings/about', [SettingsController::class, 'updateAbout'])->name('settings.about');
    Route::post('settings/adsense', [SettingsController::class, 'updateAdsense'])->name('settings.adsense');
    Route::post('migrations/run', [MigrationController::class, 'run'])->name('migration.run');
    Route::post('migrations/refresh', [MigrationController::class, 'refresh'])->name('migration.refresh');
    Route::post('migrations/rollback', [MigrationController::class, 'rollback'])->name('migration.rollback');
    Route::post('migrations/run/{migration}', [MigrationController::class, 'runSpecific'])->name('migration.run.specific');
    Route::get('migrations/download-sql', [MigrationController::class, 'downloadSql'])->name('migration.download.sql');
    Route::post('cache/clear', function () {
        Artisan::call('cache:clear');
        return redirect()->back()->with('notify', [['success', 'Application cache cleared.']]);
    })->name('cache.clear');
});
