<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\StartupController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HealthController;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Admin\PruningController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StartupController as AdminStartupController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\GatewayController;
use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Admin\SocialController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

if (! config('eden.installed') && ! env('EDEN_INSTALLED')) {
    Route::get('/install', [InstallController::class, 'index'])->name('install');
    Route::post('/install', [InstallController::class, 'store'])->name('install.store')->middleware('throttle:3,1');
    Route::get('/', fn () => redirect()->route('install'));
    Route::get('/{any}', fn () => redirect()->route('install'))->where('any', '.*');
    return;
}

Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/', HomeController::class)->name('home');
Route::get('/startups', [StartupController::class, 'index'])->name('startups.index');
Route::get('/startups/create', [StartupController::class, 'create'])->name('startups.create');
Route::post('/startups', [StartupController::class, 'store'])->name('startups.store')->middleware('throttle:5,60');
Route::get('/startups/{startup:slug}', [StartupController::class, 'show'])->name('startups.show');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show')->where('slug', '[a-z0-9\-]+');
Route::post('/newsletter', [\App\Http\Controllers\NewsletterController::class, 'store'])->name('newsletter.store')->middleware('throttle:5,1');
Route::get('/newsletter/unsubscribe', [\App\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe')->middleware('signed');
Route::post('/payment/paynow/result', \App\Http\Controllers\Payment\PaynowResultController::class)->name('payment.paynow.result');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/pro', [\App\Http\Controllers\ProController::class, 'index'])->name('pro.index');
    Route::post('/pro/checkout', [\App\Http\Controllers\ProController::class, 'checkout'])->name('pro.checkout')->middleware('throttle:5,1');
    Route::get('/payment/return/{payment}', [\App\Http\Controllers\ProController::class, 'return'])->name('payment.return');
    Route::get('/my/blog', [\App\Http\Controllers\MyBlogController::class, 'index'])->name('my.blog.index')->middleware('can:blog');
    Route::get('/my/blog/create', [\App\Http\Controllers\MyBlogController::class, 'create'])->name('my.blog.create')->middleware('can:blog');
    Route::post('/my/blog', [\App\Http\Controllers\MyBlogController::class, 'store'])->name('my.blog.store')->middleware('can:blog');
    Route::get('/my/blog/{post:slug}/edit', [\App\Http\Controllers\MyBlogController::class, 'edit'])->name('my.blog.edit')->middleware('can:blog');
    Route::put('/my/blog/{post}', [\App\Http\Controllers\MyBlogController::class, 'update'])->name('my.blog.update')->middleware('can:blog');
    Route::delete('/my/blog/{post}', [\App\Http\Controllers\MyBlogController::class, 'destroy'])->name('my.blog.destroy')->middleware('can:blog');
    Route::get('/my/startups', [\App\Http\Controllers\MyStartupController::class, 'index'])->name('my.startups.index');
    Route::get('/my/startups/{startup:slug}/edit', [\App\Http\Controllers\MyStartupController::class, 'edit'])->name('my.startups.edit');
    Route::put('/my/startups/{startup}', [\App\Http\Controllers\MyStartupController::class, 'update'])->name('my.startups.update');
    Route::get('/startups/{startup:slug}/claim', [ClaimController::class, 'create'])->name('claim.create');
    Route::post('/claim/{claim}/verify', [ClaimController::class, 'verify'])->name('claim.verify');
    Route::post('/startups/{startup}/vote', [VoteController::class, 'store'])->name('vote.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('startups', AdminStartupController::class)->except(['show']);
    Route::post('startups/{startup}/approve', [AdminStartupController::class, 'approve'])->name('startups.approve');
    Route::post('startups/{startup}/reject', [AdminStartupController::class, 'reject'])->name('startups.reject');
    Route::get('submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('ads', AdController::class);
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('migrations', [MigrationController::class, 'index'])->name('migrations.index');
    Route::post('migrations/run', [MigrationController::class, 'run'])->name('migrations.run');
    Route::get('pruning', [PruningController::class, 'index'])->name('pruning.index');
    Route::post('pruning', [PruningController::class, 'destroy'])->name('pruning.destroy');
    Route::get('health', [HealthController::class, 'index'])->name('health.index');
    Route::post('health/run/{command}', [HealthController::class, 'run'])->name('health.run');
    Route::get('gateways', [GatewayController::class, 'index'])->name('gateways.index');
    Route::put('gateways', [GatewayController::class, 'update'])->name('gateways.update');
    Route::get('blog', [AdminBlogController::class, 'index'])->name('blog.index');
    Route::get('mail', [MailController::class, 'index'])->name('mail.index');
    Route::put('mail', [MailController::class, 'update'])->name('mail.update');
    Route::get('social', [SocialController::class, 'index'])->name('social.index');
    Route::put('social', [SocialController::class, 'update'])->name('social.update');
});
