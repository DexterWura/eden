<?php

namespace App\Providers;

use App\Constants\Status;
use App\Lib\Searchable;
use App\Models\AdminNotification;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContactSubmission;
use App\Models\Startup;
use App\Models\StartupReport;
use App\Models\Frontend;
use App\Observers\BlogPostObserver;
use App\Observers\CategoryObserver;
use App\Observers\StartupObserver;
use App\Services\FounderDashboardService;
use App\Services\MigrationDriftService;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Builder::mixin(new Searchable);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Startup::observe(StartupObserver::class);
        BlogPost::observe(BlogPostObserver::class);
        Category::observe(CategoryObserver::class);
        Event::listen(MigrationsEnded::class, function (MigrationsEnded $event): void {
            $tracking = app(MigrationDriftService::class);
            if ($event->method === 'up') {
                $tracking->recordCurrentAppliedMigrations();
            } elseif ($event->method === 'down') {
                $tracking->removeTrackingForRolledBackMigrations();
            }
        });

        if (config('app.force_https', true)) {
            \URL::forceScheme('https');
        }

        // Don't redirect if we're already on the install page
        try {
            $request = request();
            if ($request && !$request->is('install*')) {
                $isInstalled = false;
                
                // Try Laravel cache first
                try {
                    $isInstalled = cache()->get('SystemInstalled');
                } catch (\Exception $e) {
                    // Cache might not be working, try alternative method
                }
                
                // If not found in cache, check file-based alternative
                if (!$isInstalled) {
                    // Check both possible locations (relative to core and absolute)
                    $cacheFile1 = base_path('storage/framework/cache/data/SystemInstalled');
                    $cacheFile2 = dirname(base_path()) . '/core/storage/framework/cache/data/SystemInstalled';
                    
                    $cacheFile = file_exists($cacheFile1) ? $cacheFile1 : (file_exists($cacheFile2) ? $cacheFile2 : null);
                    
                    if ($cacheFile && file_exists($cacheFile)) {
                        $cacheData = @unserialize(file_get_contents($cacheFile));
                        if (is_array($cacheData) && isset($cacheData['installed']) && $cacheData['installed']) {
                            $isInstalled = true;
                            // Try to set it in Laravel cache for future use
                            try {
                                cache()->put('SystemInstalled', true, now()->addYears(10));
                            } catch (\Exception $e) {
                                // Ignore if cache still doesn't work
                            }
                        }
                    }
                }
                
                if (!$isInstalled) {
                    // .env is in the parent directory of core
                    $envFilePath = dirname(base_path()) . '/.env';
                    if (!file_exists($envFilePath)) {
                        header('Location: /install');
                        exit;
                    }
                    $envContents = @file_get_contents($envFilePath);
                    if (empty(trim($envContents))) {
                        header('Location: /install');
                        exit;
                    } else {
                        // .env exists and has content, assume installed
                        // Don't try to set cache here to avoid errors
                        // Just let it through
                    }
                }
            }
        } catch (\Exception $e) {
            // If anything fails in the installation check, just continue
            // Better to show the site with potential errors than redirect loop
        }

        // Only proceed with view composers if view service is available
        if (!$this->app->bound('view')) {
            return; // View service not ready yet, skip view operations
        }
        
        // Only proceed with view composers if database is ready
        try {
            // Check if database connection is available
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                // Database not ready, use fallbacks
                $viewShare['activeTemplate'] = 'templates.basic.';
                $viewShare['activeTemplateTrue'] = 'assets/templates/basic/';
                $viewShare['emptyMessage'] = 'Data not found';
                $this->app->make('view')->share($viewShare);
                return;
            }
            
            // Try to get active template, with fallback
            try {
                $activeTemplate = activeTemplate();
                $activeTemplateTrue = activeTemplate(true);
            } catch (\Exception $e) {
                // Fallback if activeTemplate fails
                $activeTemplate = 'templates.basic.';
                $activeTemplateTrue = 'assets/templates/basic/';
            }
            
            $viewShare['activeTemplate'] = $activeTemplate;
            $viewShare['activeTemplateTrue'] = $activeTemplateTrue;
            $viewShare['emptyMessage'] = 'Data not found';
            $this->app->make('view')->share($viewShare);

            // Provide filtered sidenav to admin layout (so topnav and sidenav partials both receive it)
            $this->app->make('view')->composer(['admin.layouts.app', 'admin.partials.topnav'], function ($view) {
                try {
                    $sidenavPath = resource_path('views/admin/partials/sidenav.json');
                    $sidenavRaw = file_exists($sidenavPath) ? file_get_contents($sidenavPath) : '{}';
                    $sidenavData = json_decode($sidenavRaw, true) ?: [];
                    $admin = auth('admin')->user();
                    if ($admin && !$admin->isSuperAdmin()) {
                        $allowed = $admin->getAllowedModules();
                        $sidenavData = array_intersect_key($sidenavData, array_flip($allowed));
                    }
                    $view->with('sidenav', json_encode((object) $sidenavData));
                } catch (\Exception $e) {
                    $sidenavPath = resource_path('views/admin/partials/sidenav.json');
                    $view->with('sidenav', file_exists($sidenavPath) ? file_get_contents($sidenavPath) : '{}');
                }
            });

            $this->app->make('view')->composer('admin.partials.sidenav', function ($view) {
                try {
                    $sidenavPath = resource_path('views/admin/partials/sidenav.json');
                    $sidenavRaw = file_exists($sidenavPath) ? file_get_contents($sidenavPath) : '{}';
                    $sidenavData = json_decode($sidenavRaw, true) ?: [];
                    $admin = auth('admin')->user();
                    if ($admin && method_exists($admin, 'isSuperAdmin') && !$admin->isSuperAdmin() && method_exists($admin, 'getAllowedModules')) {
                        $allowed = $admin->getAllowedModules();
                        $sidenavData = array_intersect_key($sidenavData, array_flip($allowed));
                    }
                    $sidenavJson = json_encode((object) $sidenavData);
                    $view->with([
                        'sidenav' => $sidenavJson,
                        'bannedUsersCount' => 0,
                        'emailUnverifiedUsersCount' => 0,
                        'mobileUnverifiedUsersCount' => 0,
                        'kycUnverifiedUsersCount' => 0,
                        'kycPendingUsersCount' => 0,
                        'pendingTicketCount' => 0,
                        'pendingDepositsCount' => 0,
                        'pendingWithdrawCount' => 0,
                        'disputedEscrowCount' => 0,
                        'pendingListingsCount' => 0,
                        'pendingOffersCount' => 0,
                        'pendingReviewsCount' => 0,
                        'updateAvailable' => false,
                    ]);
                } catch (\Exception $e) {
                    $sidenavPath = resource_path('views/admin/partials/sidenav.json');
                    $sidenavRaw = file_exists($sidenavPath) ? file_get_contents($sidenavPath) : '{}';
                    $view->with([
                        'sidenav' => $sidenavRaw,
                        'bannedUsersCount' => 0,
                        'emailUnverifiedUsersCount' => 0,
                        'mobileUnverifiedUsersCount' => 0,
                        'kycUnverifiedUsersCount' => 0,
                        'kycPendingUsersCount' => 0,
                        'pendingTicketCount' => 0,
                        'pendingDepositsCount' => 0,
                        'pendingWithdrawCount' => 0,
                        'disputedEscrowCount' => 0,
                        'pendingListingsCount' => 0,
                        'pendingOffersCount' => 0,
                        'pendingReviewsCount' => 0,
                        'updateAvailable' => false,
                    ]);
                }
            });

            $this->app->make('view')->composer('templates.*.user.partials.sidenav', function ($view) {
                $view->with([
                    'pendingDealsCount' => 0,
                    'pendingReceivedOffersCount' => 0,
                    'wonAuctionsCount' => 0,
                ]);
            });

            $this->app->make('view')->composer('admin.partials.topnav', function ($view) {
                try {
                    $view->with([
                        'adminNotifications' => AdminNotification::where('is_read', Status::NO)->with('user')->orderBy('id', 'desc')->take(10)->get(),
                        'adminNotificationCount' => AdminNotification::where('is_read', Status::NO)->count(),
                    ]);
                } catch (\Exception $e) {
                    $view->with([
                        'adminNotifications' => collect(),
                        'adminNotificationCount' => 0,
                    ]);
                }
            });

            $this->app->make('view')->composer('eden.layout-dashboard', function ($view) {
                $data = $view->getData();
                if (($data['sidebar'] ?? '') === 'founder' && auth()->check()) {
                    if (array_key_exists('founderNavStatus', $data)) {
                        return;
                    }
                    try {
                        $view->with('founderNavStatus', app(FounderDashboardService::class)->navigationStatus(auth()->user()));
                    } catch (\Throwable $exception) {
                        $view->with('founderNavStatus', []);
                    }
                    return;
                }
                if (($data['sidebar'] ?? '') !== 'admin') {
                    return;
                }
                try {
                    $lastSawStartups = session('admin_last_saw_startups', '1970-01-01 00:00:00');
                    $admin = auth()->guard('admin')->user();
                    $lastSawMessages = $admin && $admin->last_saw_contact_messages_at
                        ? $admin->last_saw_contact_messages_at->toDateTimeString()
                        : '1970-01-01 00:00:00';
                    $view->with([
                        'adminPendingStartupsBadge' => Startup::pending()->where('created_at', '>', $lastSawStartups)->count(),
                        'adminUnseenMessagesBadge' => ContactSubmission::where('created_at', '>', $lastSawMessages)->count(),
                        'adminPendingListingReportsBadge' => StartupReport::where('status', StartupReport::STATUS_PENDING)->count(),
                    ]);
                } catch (\Exception $e) {
                    $view->with([
                        'adminPendingStartupsBadge' => 0,
                        'adminUnseenMessagesBadge' => 0,
                        'adminPendingListingReportsBadge' => 0,
                    ]);
                }
            });

            $this->app->make('view')->composer('eden.layout', function ($view) {
                try {
                    $footerCategories = cache()->remember('eden:footer-categories', now()->addHour(), function () {
                        return Category::query()
                            ->withCount([
                                'startups as active_startups_count' => fn ($query) => $query->active(),
                            ])
                            ->orderByDesc('active_startups_count')
                            ->orderBy('sort_order')
                            ->limit(16)
                            ->get(['id', 'name', 'slug']);
                    });
                    $view->with('footerCategories', $footerCategories);
                } catch (\Exception $e) {
                    $view->with('footerCategories', collect());
                }
            });

            $this->app->make('view')->composer('partials.seo', function ($view) {
                try {
                    $seo = Frontend::where('data_keys', 'seo.data')->first();
                    $view->with([
                        'seo' => $seo ? $seo->data_values : $seo,
                    ]);
                } catch (\Exception $e) {
                    $view->with(['seo' => null]);
                }
            });
        } catch (\Exception $e) {
            // Database not ready, set minimal view shares if view service is available
            if ($this->app->bound('view')) {
                try {
                    $viewShare['activeTemplate'] = 'templates.basic.';
                    $viewShare['activeTemplateTrue'] = 'assets/templates/basic/';
                    $viewShare['emptyMessage'] = 'Data not found';
                    $this->app->make('view')->share($viewShare);
                } catch (\Exception $e2) {
                    // Ignore if view sharing fails
                }
            }
        }


        // LinkedIn OpenID Connect: use app provider aligned with Microsoft Learn docs
        Socialite::extend('linkedin-openid', function ($app) {
            $config = $app['config']['services.linkedin-openid'];
            return new \App\Lib\LinkedInOpenIdProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });

        Paginator::useBootstrapFive();
    }
}
