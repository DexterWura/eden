<?php

use App\Http\Controllers\Api\Eden\FlipitController as EdenFlipitController;
use App\Http\Controllers\Api\Eden\RevenueController as EdenRevenueController;
use App\Http\Controllers\Api\Eden\TrafficTrackController as EdenTrafficTrackController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ScheduledTasksController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StartupController as AdminStartupController;
use App\Http\Controllers\Admin\StartupWebsiteHealthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Eden\AuthController;
use App\Http\Controllers\Eden\ClaimController;
use App\Http\Controllers\Eden\DashboardController;
use App\Http\Controllers\Eden\HomeController;
use App\Http\Controllers\Eden\PageController;
use App\Http\Controllers\Eden\BlogController;
use App\Http\Controllers\Eden\StartupController;
use App\Http\Controllers\Eden\StartupCommentController;
use App\Http\Controllers\Eden\SavedStartupController;
use App\Http\Controllers\Eden\LaunchNotifyController;
use App\Http\Controllers\Eden\AnalyticsController;
use App\Http\Controllers\Eden\BadgeController;
use App\Http\Controllers\User\Auth\SocialiteController;
use App\Http\Controllers\Founder\BadgesController as FounderBadgesController;
use App\Http\Controllers\Founder\RevenueApiController as FounderRevenueApiController;
use App\Http\Controllers\Founder\SettingsController as FounderSettingsController;
use App\Http\Controllers\Founder\StartupController as FounderStartupController;
use App\Http\Controllers\Founder\UpvotesController;
use App\Http\Controllers\Founder\BlogController as FounderBlogController;
use App\Http\Controllers\Eden\PricingController;
use App\Http\Controllers\Eden\FeedController;
use App\Http\Controllers\Eden\LinkedInAuthController;
use App\Http\Controllers\Admin\GatewayController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::post('api/eden/v1/revenue', [EdenRevenueController::class, 'record'])
    ->middleware(['throttle:60,1', 'eden.revenue.api'])
    ->name('api.eden.revenue.record');

Route::post('api/eden/v1/flipit/listing-sold', [EdenFlipitController::class, 'listingSold'])
    ->middleware(['throttle:30,1'])
    ->name('api.eden.flipit.listing-sold');

Route::get('api/eden/v1/track.js', [EdenTrafficTrackController::class, 'script'])->name('api.eden.track.script');
Route::get('api/eden/v1/track', [EdenTrafficTrackController::class, 'track'])->middleware('throttle:120,1')->name('api.eden.track');

// Admin login accessible at /backoffice (shows login for guests)
Route::get('/backoffice', [AdminLoginController::class, 'showLoginForm'])->name('admin.login')->middleware('admin.guest');
Route::post('/backoffice', [AdminLoginController::class, 'login'])->middleware(['admin.guest', 'throttle:5,1']);
Route::get('/backoffice/logout', [AdminLoginController::class, 'logout'])->name('admin.logout')->middleware('admin');
Route::get('/backoffice/password/reset', fn () => redirect()->route('admin.login'))->name('admin.password.reset');

Route::redirect('/admin-dashboard', '/backoffice/dashboard', 301);
Route::redirect('/founder-dashboard', '/founder', 301);
Route::redirect('/startup', '/founder', 301);

Route::get('/cron', function () {
    $secret = env('CRON_SECRET');
    if ($secret !== '' && $secret !== null) {
        $provided = request()->query('secret') ?? request()->header('X-Cron-Secret');
        if (!hash_equals((string) $secret, (string) $provided)) {
            abort(403);
        }
    }
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    return response('', 204);
})->middleware('throttle:60,1')->name('cron');

Route::get('/', [HomeController::class, 'index']);
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/raising', [HomeController::class, 'raising'])->name('raising');
Route::get('/for-sale', [HomeController::class, 'forSale'])->name('for-sale');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/about', [PageController::class, 'about']);
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/contact', [PageController::class, 'contact']);
Route::post('/contact', [PageController::class, 'contactStore'])->middleware('throttle:10,1');
Route::get('/submit', [PageController::class, 'submit']);
Route::post('/submit', [PageController::class, 'submitStore'])->middleware('throttle:5,1');
Route::get('/categories', [PageController::class, 'categories']);
Route::post('/subscribe', [PageController::class, 'subscribe'])->middleware('throttle:10,1');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::post('/checkout', [PricingController::class, 'checkout'])->middleware('auth');
Route::get('/checkout/paypal/return', [PricingController::class, 'paypalReturn'])->middleware('auth');
Route::get('/checkout/paypal/cancel', [PricingController::class, 'paypalCancel'])->middleware('auth');
Route::get('/checkout/paynow/return', [PricingController::class, 'paynowReturn'])->middleware('auth');
Route::any('/checkout/paynow/callback', [PricingController::class, 'paynowCallback']);
Route::get('/feed/new', [FeedController::class, 'new'])->name('feed.new');
Route::get('/feed/featured', [FeedController::class, 'featured'])->name('feed.featured');
Route::get('/launching-today', [StartupController::class, 'launchingToday']);
Route::get('/badge/{type}', [BadgeController::class, 'show'])->name('badge.show')->where('type', 'listed|featured|product-of-day');
Route::get('/startup/{slug}', [StartupController::class, 'show'])->name('startup.show');
Route::get('/startup/{slug}/notify', [LaunchNotifyController::class, 'show'])->name('launch-notify.show');
Route::post('/startup/{slug}/notify', [LaunchNotifyController::class, 'store'])->middleware('throttle:10,1')->name('launch-notify.store');
Route::get('/startup/{slug}/out', [StartupController::class, 'out']);
Route::post('/startup/{slug}/upvote', [StartupController::class, 'upvote'])->middleware('throttle:30,1')->name('startup.upvote');
Route::post('/startup/{slug}/save', [SavedStartupController::class, 'save'])->name('startup.save')->middleware('auth');
Route::post('/startup/{slug}/unsave', [SavedStartupController::class, 'unsave'])->name('startup.unsave')->middleware('auth');
Route::post('/startup/{slug}/comment', [StartupCommentController::class, 'store'])->name('startup.comment')->middleware('auth');
Route::get('/startup/{slug}/claim', [ClaimController::class, 'show'])->name('startup.claim');
Route::post('/startup/{slug}/claim/confirm', [ClaimController::class, 'confirm'])->name('startup.claim.confirm')->middleware('auth');
Route::post('/startup/{slug}/claim/start', [ClaimController::class, 'startVerification'])->name('startup.claim.start')->middleware('auth');
Route::post('/startup/{slug}/claim/verify', [ClaimController::class, 'verify'])->name('startup.claim.verify')->middleware('auth');
Route::get('/saved', [SavedStartupController::class, 'index'])->name('saved')->middleware('auth');
Route::middleware('auth')->prefix('founder')->name('founder.')->group(function () {
    Route::get('/', [DashboardController::class, 'founderDashboard'])->name('dashboard');
    Route::get('startups', [FounderStartupController::class, 'index'])->name('startups.index');
    Route::get('startups/create', [FounderStartupController::class, 'create'])->name('startups.create');
    Route::post('startups', [FounderStartupController::class, 'store'])->name('startups.store');
    Route::get('startups/{startup}/edit', [FounderStartupController::class, 'edit'])->name('startups.edit');
    Route::put('startups/{startup}', [FounderStartupController::class, 'update'])->name('startups.update');
    Route::delete('startups/{startup}', [FounderStartupController::class, 'destroy'])->name('startups.destroy');
    Route::post('startups/{startup}/toggle-featured', [FounderStartupController::class, 'toggleFeatured'])->name('startups.toggle-featured');
    Route::get('blog', [FounderBlogController::class, 'index'])->name('blog.index');
    Route::get('blog/create', [FounderBlogController::class, 'create'])->name('blog.create');
    Route::post('blog', [FounderBlogController::class, 'store'])->name('blog.store');
    Route::get('blog/{post}/edit', [FounderBlogController::class, 'edit'])->name('blog.edit');
    Route::put('blog/{post}', [FounderBlogController::class, 'update'])->name('blog.update');
    Route::delete('blog/{post}', [FounderBlogController::class, 'destroy'])->name('blog.destroy');
    Route::get('upvotes', [UpvotesController::class, 'index'])->name('upvotes');
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('analytics/export/csv', [AnalyticsController::class, 'exportCsv'])->name('analytics.export.csv');
    Route::get('analytics/export/pdf', [AnalyticsController::class, 'exportPdf'])->name('analytics.export.pdf');
    Route::get('badges', [FounderBadgesController::class, 'index'])->name('badges');
    Route::get('revenue-api', [FounderRevenueApiController::class, 'index'])->name('revenue-api');
    Route::post('revenue-api/startups/{startup}/create-key', [FounderRevenueApiController::class, 'createKey'])->name('revenue-api.create-key');
    Route::post('revenue-api/startups/{startup}/regenerate-key', [FounderRevenueApiController::class, 'regenerateKey'])->name('revenue-api.regenerate-key');
    Route::post('revenue-api/startups/{startup}/integrations', [FounderRevenueApiController::class, 'connectIntegration'])->name('revenue-api.connect-integration');
    Route::delete('revenue-api/startups/{startup}/integrations/{gateway}', [FounderRevenueApiController::class, 'disconnectIntegration'])->name('revenue-api.disconnect-integration')->where('gateway', 'stripe|polar|lemonsqueezy');
    Route::post('revenue-api/startups/{startup}/sync', [FounderRevenueApiController::class, 'syncNow'])->name('revenue-api.sync');
    Route::get('settings', [FounderSettingsController::class, 'index'])->name('settings');
    Route::put('settings', [FounderSettingsController::class, 'update'])->name('settings.update');
    Route::post('hero-request/{startup}', [DashboardController::class, 'requestHeroFeature'])->name('hero-request');
    Route::post('notifications/{notification}/dismiss', function (string $notification) {
        $n = auth()->user()->notifications()->where('id', $notification)->first();
        if ($n) {
            $n->markAsRead();
        }
        return redirect()->route('founder.dashboard');
    })->name('notifications.dismiss');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/social/{provider}', [SocialiteController::class, 'socialLogin'])->name('user.social.login')->where('provider', 'google|facebook|linkedin|twitter');
Route::get('/auth/social/callback/{provider}', [SocialiteController::class, 'callback'])->name('user.social.login.callback')->where('provider', 'google|facebook|linkedin|twitter');
Route::get('/auth/linkedin', [LinkedInAuthController::class, 'redirect'])->name('eden.linkedin.redirect')->middleware('guest');
Route::get('/auth/linkedin/callback', [LinkedInAuthController::class, 'callback'])->name('eden.linkedin.callback');

Route::middleware('admin')->prefix('backoffice')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
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
    Route::post('startups/{startup}/feature-on-hero', [AdminStartupController::class, 'toggleFeaturedOnHero'])->name('startups.toggle-hero');
    Route::post('hero-request/{startup}/approve', [DashboardController::class, 'approveHeroRequest'])->name('hero-request.approve');
    Route::post('hero-request/{startup}/reject', [DashboardController::class, 'rejectHeroRequest'])->name('hero-request.reject');
    Route::delete('startups/{startup}', [AdminStartupController::class, 'destroy'])->name('startups.destroy');
    Route::get('startup-websites', [StartupWebsiteHealthController::class, 'index'])->name('startup-websites.index');
    Route::post('startup-websites/run-check', [StartupWebsiteHealthController::class, 'runCheck'])->name('startup-websites.run-check');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/gift-pro', [UserController::class, 'giftPro'])->name('users.gift-pro');
    Route::get('users/{user}/startups', [UserController::class, 'startups'])->name('users.startups');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('blog', [BlogPostController::class, 'index'])->name('blog.index');
    Route::get('blog/create', [BlogPostController::class, 'create'])->name('blog.create');
    Route::post('blog', [BlogPostController::class, 'store'])->name('blog.store');
    Route::get('blog/{post}', [BlogPostController::class, 'edit'])->name('blog.edit');
    Route::put('blog/{post}', [BlogPostController::class, 'update'])->name('blog.update');
    Route::delete('blog/{post}', [BlogPostController::class, 'destroy'])->name('blog.destroy');
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('contact-messages/{submission}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::post('contact-messages/{submission}/reply', [ContactMessageController::class, 'reply'])->name('contact-messages.reply');
    Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    Route::get('subscribers/import', [SubscriberController::class, 'import'])->name('subscribers.import');
    Route::post('subscribers/import', [SubscriberController::class, 'importStore'])->name('subscribers.import.store');
    Route::get('subscribers/compose', [SubscriberController::class, 'compose'])->name('subscribers.compose');
    Route::post('subscribers/preview', [SubscriberController::class, 'preview'])->name('subscribers.preview');
    Route::post('subscribers/send', [SubscriberController::class, 'send'])->name('subscribers.send');
    Route::delete('subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('scheduled-tasks', [ScheduledTasksController::class, 'index'])->name('scheduled-tasks.index');
    Route::put('scheduled-tasks/{task}', [ScheduledTasksController::class, 'update'])->name('scheduled-tasks.update');
    Route::post('scheduled-tasks/{task}/run', [ScheduledTasksController::class, 'runNow'])->name('scheduled-tasks.run');
    Route::get('seo', [SettingsController::class, 'seo'])->name('seo');
    Route::get('about', [SettingsController::class, 'aboutPage'])->name('about');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/email', [SettingsController::class, 'email'])->name('settings.email');
    Route::post('settings/seo', [SettingsController::class, 'updateSeo'])->name('settings.seo');
    Route::post('settings/about', [SettingsController::class, 'updateAbout'])->name('settings.about');
    Route::post('settings/adsense', [SettingsController::class, 'updateAdsense'])->name('settings.adsense');
    Route::post('settings/linkedin', [SettingsController::class, 'updateLinkedIn'])->name('settings.linkedin');
    Route::post('settings/robots', [SettingsController::class, 'updateRobots'])->name('settings.robots');
    Route::post('settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
    Route::post('migrations/run', [MigrationController::class, 'run'])->name('migration.run');
    Route::post('migrations/refresh', [MigrationController::class, 'refresh'])->name('migration.refresh');
    Route::post('migrations/rollback', [MigrationController::class, 'rollback'])->name('migration.rollback');
    Route::post('migrations/run/{migration}', [MigrationController::class, 'runSpecific'])->name('migration.run.specific');
    Route::get('migrations/download-sql', [MigrationController::class, 'downloadSql'])->name('migration.download.sql');
    Route::get('gateways', [GatewayController::class, 'index'])->name('gateways.index');
    Route::get('gateways/{gateway}/edit', [GatewayController::class, 'edit'])->name('gateways.edit');
    Route::put('gateways/{gateway}', [GatewayController::class, 'update'])->name('gateways.update');
    Route::post('gateways/seed', [GatewayController::class, 'seed'])->name('gateways.seed');
    Route::post('cache/clear', function () {
        Artisan::call('cache:clear');
        return redirect()->back()->with('notify', [['success', 'Application cache cleared.']]);
    })->name('cache.clear');
});
