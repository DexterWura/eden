<?php

use Illuminate\Support\Facades\Route;


Route::namespace('Auth')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::controller('LoginController')->group(function () {
            Route::get('/', 'showLoginForm')->name('login');
            Route::post('/', 'login')->name('login');
            Route::get('logout', 'logout')->middleware('admin')->withoutMiddleware('admin.guest')->name('logout');
        });

        // Admin Password Reset
        Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
            Route::get('reset', 'showLinkRequestForm')->name('reset');
            Route::post('reset', 'sendResetCodeEmail');
            Route::get('code-verify', 'codeVerify')->name('code.verify');
            Route::post('verify-code', 'verifyCode')->name('verify.code');
        });

        Route::controller('ResetPasswordController')->group(function () {
            Route::get('password/reset/{token}', 'showResetForm')->name('password.reset.form');
            Route::post('password/reset/change', 'reset')->name('password.change');
        });
    });
});

Route::middleware(['admin', 'admin.module', 'demo.mode'])->group(function () {
    Route::controller('AdminController')->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('chart/listings', 'listingsReport')->name('chart.listings');
        Route::get('chart/bids', 'bidsReport')->name('chart.bids');
        Route::get('chart/deposit-withdraw', 'depositAndWithdrawReport')->name('chart.deposit.withdraw');
        Route::get('chart/transaction', 'transactionReport')->name('chart.transaction');
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');
        Route::get('password', 'password')->name('password');
        Route::post('password', 'passwordUpdate')->name('password.update');

        //Notification
        Route::get('notifications', 'notifications')->name('notifications');
        Route::get('notification/read/{id}', 'notificationRead')->name('notification.read');
        Route::get('notifications/read-all', 'readAllNotification')->name('notifications.read.all');
        Route::post('notifications/delete-all', 'deleteAllNotification')->name('notifications.delete.all');
        Route::post('notifications/delete-single/{id}', 'deleteSingleNotification')->name('notifications.delete.single');

        //Report Bugs
        Route::get('request-report', 'requestReport')->name('request.report');
        Route::post('request-report', 'reportSubmit')->name('request.report.submit');

        Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
    });

    // Staff management (super admin only; controller enforces)
    Route::controller('StaffController')->name('staff.')->prefix('staff')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('delete/{id}', 'destroy')->name('destroy');
    });

    // Audit log (super admin only; controller enforces)
    Route::controller('AuditLogController')->name('audit.')->prefix('audit')->group(function () {
        Route::get('/', 'index')->name('index');
    });

    // Users Manager
    Route::controller('ManageUsersController')->name('users.')->prefix('users')->group(function () {
        Route::get('/', 'allUsers')->name('all');
        Route::get('active', 'activeUsers')->name('active');
        Route::get('banned', 'bannedUsers')->name('banned');
        Route::get('email-verified', 'emailVerifiedUsers')->name('email.verified');
        Route::get('email-unverified', 'emailUnverifiedUsers')->name('email.unverified');
        Route::get('mobile-unverified', 'mobileUnverifiedUsers')->name('mobile.unverified');
        Route::get('kyc-unverified', 'kycUnverifiedUsers')->name('kyc.unverified');
        Route::get('kyc-pending', 'kycPendingUsers')->name('kyc.pending');
        Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');
        Route::get('with-balance', 'usersWithBalance')->name('with.balance');

        Route::get('detail/{id}', 'detail')->name('detail');
        Route::get('kyc-data/{id}', 'kycDetails')->name('kyc.details');
        Route::post('kyc-approve/{id}', 'kycApprove')->name('kyc.approve');
        Route::post('kyc-reject/{id}', 'kycReject')->name('kyc.reject');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('add-sub-balance/{id}', 'addSubBalance')->name('add.sub.balance');
        Route::get('send-notification/{id}', 'showNotificationSingleForm')->name('notification.single');
        Route::post('send-notification/{id}', 'sendNotificationSingle')->name('notification.single');
        Route::get('login/{id}', 'login')->name('login');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('toggle-test-user/{id}', 'toggleTestUser')->name('toggle.test.user');

        Route::get('send-notification', 'showNotificationAllForm')->name('notification.all');
        Route::post('send-notification', 'sendNotificationAll')->name('notification.all.send');
        Route::get('list', 'list')->name('list');
        Route::get('count-by-segment/{methodName}', 'countBySegment')->name('segment.count');
        Route::get('notification-log/{id}', 'notificationLog')->name('notification.log');
    });


    //Manage Category
    Route::controller('CategoryController')->prefix('category')->name('category.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
    });

    //Escrow Manage

    Route::controller('EscrowController')->name('escrow.')->prefix('escrow')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('accepted', 'accepted')->name('accepted');
        Route::get('not-accepted', 'notAccepted')->name('not.accepted');
        Route::get('completed', 'completed')->name('completed');
        Route::get('disputed', 'disputed')->name('disputed');
        Route::get('canceled', 'canceled')->name('canceled');

        Route::get('details/{id}', 'details')->name('details');
        Route::get('milestone/{id}', 'milestone')->name('milestone');
        Route::post('message', 'replyMessage')->name('message.reply');
        Route::get('message-get', 'getMessage')->name('message.get');

        Route::post('action', 'action')->name('action');
    });


    //Escrow Charge
    Route::controller('ChargeController')->prefix('charge')->name('charge.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('global', 'globalCharge')->name('global');
        Route::post('marketplace-fees', 'marketplaceFees')->name('marketplace.fees');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('remove/{id}', 'remove')->name('remove');
    });

    // ==================== MARKETPLACE MANAGEMENT ====================

    // Listing Categories
    Route::controller('ListingCategoryController')->prefix('listing-category')->name('listing.category.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('delete/{id}', 'delete')->name('delete');
    });

    // Listings Management
    Route::controller('ListingController')->prefix('listings')->name('listing.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('active', 'active')->name('active');
        Route::get('sold', 'sold')->name('sold');
        Route::get('rejected', 'rejected')->name('rejected');
        Route::get('expired', 'expired')->name('expired');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('approve/{id}', 'approve')->name('approve');
        Route::post('reject/{id}', 'reject')->name('reject');
        Route::post('feature/{id}', 'feature')->name('feature');
        Route::post('unfeature/{id}', 'unfeature')->name('unfeature');
        Route::post('verify/{id}', 'verify')->name('verify');
        Route::post('extend-auction/{id}', 'extendAuction')->name('extend.auction');
        Route::post('cancel/{id}', 'cancel')->name('cancel');
        Route::post('deactivate/{id}', 'deactivate')->name('deactivate');
        Route::post('reactivate/{id}', 'reactivate')->name('reactivate');
    });

    // Marketplace Homepage Settings
    Route::controller('MarketplaceSettingController')->prefix('marketplace-settings')->name('marketplace.')->group(function () {
        Route::get('sections', 'sections')->name('sections');
        Route::post('section/update/{id}', 'updateSection')->name('section.update');
        Route::post('section/toggle/{id}', 'toggleSection')->name('section.toggle');
    });

    // Marketplace Fees (named fees, payer control)
    Route::controller('MarketplaceFeeController')->prefix('marketplace-fees')->name('marketplace.fee.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('remove/{id}', 'remove')->name('remove');
    });

    // Bids Management
    Route::controller('BidController')->prefix('bids')->name('bid.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('winning', 'winning')->name('winning');
        Route::get('won', 'won')->name('won');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('cancel/{id}', 'cancel')->name('cancel');
        Route::post('process-auction/{listingId}', 'processAuctionEnd')->name('process.auction');
    });

    // Offers Management
    Route::controller('OfferController')->prefix('offers')->name('offer.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('accepted', 'accepted')->name('accepted');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('cancel/{id}', 'cancel')->name('cancel');
    });


    // Reviews Management
    Route::controller('ReviewController')->prefix('reviews')->name('review.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('approve/{id}', 'approve')->name('approve');
        Route::post('hide/{id}', 'hide')->name('hide');
        Route::post('delete/{id}', 'delete')->name('delete');
    });

    // User Reports (listing / user)
    Route::controller('ReportController')->prefix('reports')->name('report.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('show/{id}', 'show')->name('show');
        Route::post('update/{id}', 'update')->name('update');
    });

    // Subscriber
    Route::controller('SubscriberController')->prefix('subscriber')->name('subscriber.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('send-email', 'sendEmailForm')->name('send.email');
        Route::post('remove/{id}', 'remove')->name('remove');
        Route::post('send-email', 'sendEmail')->name('send.email');
    });

    // Deposit Gateway
    Route::name('gateway.')->prefix('gateway')->group(function () {
        // Automatic Gateway
        Route::controller('AutomaticGatewayController')->prefix('automatic')->name('automatic.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{code}', 'update')->name('update');
            Route::post('remove/{id}', 'remove')->name('remove');
            Route::post('status/{id}', 'status')->name('status');
        });


        // Manual Methods
        Route::controller('ManualGatewayController')->prefix('manual')->name('manual.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('new', 'create')->name('create');
            Route::post('new', 'store')->name('store');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });


    // DEPOSIT SYSTEM
    Route::controller('DepositController')->prefix('deposit')->name('deposit.')->group(function () {
        Route::get('all/{user_id?}', 'deposit')->name('list');
        Route::get('pending/{user_id?}', 'pending')->name('pending');
        Route::get('rejected/{user_id?}', 'rejected')->name('rejected');
        Route::get('approved/{user_id?}', 'approved')->name('approved');
        Route::get('successful/{user_id?}', 'successful')->name('successful');
        Route::get('initiated/{user_id?}', 'initiated')->name('initiated');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('reject', 'reject')->name('reject');
        Route::post('approve/{id}', 'approve')->name('approve');
    });


    // WITHDRAW SYSTEM
    Route::name('withdraw.')->prefix('withdraw')->group(function () {

        Route::controller('WithdrawalController')->name('data.')->group(function () {
            Route::get('pending/{user_id?}', 'pending')->name('pending');
            Route::get('approved/{user_id?}', 'approved')->name('approved');
            Route::get('rejected/{user_id?}', 'rejected')->name('rejected');
            Route::get('all/{user_id?}', 'all')->name('all');
            Route::get('details/{id}', 'details')->name('details');
            Route::post('approve', 'approve')->name('approve');
            Route::post('reject', 'reject')->name('reject');
        });


        // Withdraw Method
        Route::controller('WithdrawMethodController')->prefix('method')->name('method.')->group(function () {
            Route::get('/', 'methods')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('create', 'store')->name('store');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('edit/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });

    // Report
    Route::controller('ReportController')->prefix('report')->name('report.')->group(function () {
        Route::get('transaction/{user_id?}', 'transaction')->name('transaction');
        Route::get('login/history', 'loginHistory')->name('login.history');
        Route::get('login/ipHistory/{ip}', 'loginIpHistory')->name('login.ipHistory');
        Route::get('notification/history', 'notificationHistory')->name('notification.history');
        Route::get('email/detail/{id}', 'emailDetails')->name('email.details');
        Route::get('affiliate', 'affiliate')->name('affiliate');
    });

    // NDA Management
    Route::controller('NdaController')->prefix('nda')->name('nda.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('show/{id}', 'show')->name('show');
        Route::get('audit-logs/{id}', 'auditLogs')->name('audit-logs');
        Route::get('export', 'export')->name('export');
    });


    // Admin Support
    Route::controller('SupportTicketController')->prefix('ticket')->name('ticket.')->group(function () {
        Route::get('/', 'tickets')->name('index');
        Route::get('pending', 'pendingTicket')->name('pending');
        Route::get('closed', 'closedTicket')->name('closed');
        Route::get('answered', 'answeredTicket')->name('answered');
        Route::get('view/{id}', 'ticketReply')->name('view');
        Route::post('reply/{id}', 'replyTicket')->name('reply');
        Route::post('close/{id}', 'closeTicket')->name('close');
        Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
        Route::post('delete/{id}', 'ticketDelete')->name('delete');
    });


    // Language Manager
    Route::controller('LanguageController')->prefix('language')->name('language.')->group(function () {
        Route::get('/', 'langManage')->name('manage');
        Route::post('/', 'langStore')->name('manage.store');
        Route::post('delete/{id}', 'langDelete')->name('manage.delete');
        Route::post('update/{id}', 'langUpdate')->name('manage.update');
        Route::get('edit/{id}', 'langEdit')->name('key');
        Route::post('import', 'langImport')->name('import.lang');
        Route::post('store/key/{id}', 'storeLanguageJson')->name('store.key');
        Route::post('delete/key/{id}', 'deleteLanguageJson')->name('delete.key');
        Route::post('update/key/{id}', 'updateLanguageJson')->name('update.key');
        Route::get('get-keys', 'getKeys')->name('get.key');
    });

    Route::controller('GeneralSettingController')->group(function () {

        Route::get('system-setting', 'systemSetting')->name('setting.system');

        // Affiliate Setting
        Route::get('setting/affiliate', 'affiliateSetting')->name('setting.affiliate');
        Route::post('setting/affiliate', 'affiliateSettingUpdate');

        // General Setting
        Route::get('general-setting', 'general')->name('setting.general');
        Route::post('general-setting', 'generalUpdate');

        Route::get('setting/social/credentials', 'socialiteCredentials')->name('setting.socialite.credentials');
        Route::post('setting/social/credentials/update/{key}', 'updateSocialiteCredential')->name('setting.socialite.credentials.update');
        Route::post('setting/social/credentials/status/{key}', 'updateSocialiteCredentialStatus')->name('setting.socialite.credentials.status.update');

        //configuration
        Route::get('setting/system-configuration', 'systemConfiguration')->name('setting.system.configuration');
        Route::post('setting/system-configuration', 'systemConfigurationSubmit');

        // Logo-Icon
        Route::get('setting/logo-icon', 'logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo-icon', 'logoIconUpdate')->name('setting.logo.icon');

        //Custom CSS
        Route::get('custom-css', 'customCss')->name('setting.custom.css');
        Route::post('custom-css', 'customCssSubmit');

        // Google AdSense
        Route::get('setting/adsense', 'adsenseSetting')->name('setting.adsense');
        Route::post('setting/adsense', 'adsenseSettingSubmit');

        Route::get('sitemap', 'sitemap')->name('setting.sitemap');
        Route::post('sitemap', 'sitemapSubmit');
        Route::post('sitemap/regenerate', 'regenerateSitemap')->name('setting.sitemap.regenerate');

        //Robots.txt
        Route::get('robot', 'robot')->name('setting.robot');
        Route::post('robot', 'robotSubmit');
        Route::post('robot/regenerate', 'regenerateRobots')->name('setting.robot.regenerate');

        //Cookie
        Route::get('cookie', 'cookie')->name('setting.cookie');
        Route::post('cookie', 'cookieSubmit');

        //maintenance_mode
        Route::get('maintenance-mode', 'maintenanceMode')->name('maintenance.mode');
        Route::post('maintenance-mode', 'maintenanceModeSubmit');
    });

    // Cronjob Management
    Route::controller('CronjobController')->prefix('setting')->name('setting.cronjob.')->group(function () {
        Route::get('cronjob', 'index')->name('index');
        Route::get('cronjob/log/{jobName}', 'viewLog')->name('log');
        Route::post('cronjob/update', 'update')->name('update');
        Route::post('cronjob/run', 'runAuctionProcessing')->name('run');
        Route::post('cronjob/clear-logs', 'clearLogs')->name('clear.logs');
        Route::get('cronjob/status', 'getStatus')->name('status');
    });




    //KYC setting
    Route::controller('KycController')->group(function () {
        Route::get('kyc-setting', 'setting')->name('kyc.setting');
        Route::post('kyc-setting', 'settingUpdate');
    });

    //Notification Setting
    Route::name('setting.notification.')->controller('NotificationController')->prefix('notification')->group(function () {
        //Template Setting
        Route::get('global/email', 'globalEmail')->name('global.email');
        Route::post('global/email/update', 'globalEmailUpdate')->name('global.email.update');

        Route::get('global/sms', 'globalSms')->name('global.sms');
        Route::post('global/sms/update', 'globalSmsUpdate')->name('global.sms.update');

        Route::get('global/push', 'globalPush')->name('global.push');
        Route::post('global/push/update', 'globalPushUpdate')->name('global.push.update');

        Route::get('templates', 'templates')->name('templates');
        Route::get('template/edit/{type}/{id}', 'templateEdit')->name('template.edit');
        Route::post('template/update/{type}/{id}', 'templateUpdate')->name('template.update');

        //Email Setting
        Route::get('email/setting', 'emailSetting')->name('email');
        Route::post('email/setting', 'emailSettingUpdate');
        Route::post('email/test', 'emailTest')->name('email.test');

        //SMS Setting
        Route::get('sms/setting', 'smsSetting')->name('sms');
        Route::post('sms/setting', 'smsSettingUpdate');
        Route::post('sms/test', 'smsTest')->name('sms.test');

        Route::get('notification/push/setting', 'pushSetting')->name('push');
        Route::post('notification/push/setting', 'pushSettingUpdate');
        Route::post('notification/push/setting/upload', 'pushSettingUpload')->name('push.upload');
        Route::get('notification/push/setting/download', 'pushSettingDownload')->name('push.download');
    });

    // Plugin
    Route::controller('ExtensionController')->prefix('extensions')->name('extensions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('status/{id}', 'status')->name('status');
    });


    //System Information
    Route::controller('SystemController')->name('system.')->prefix('system')->group(function () {
        Route::get('info', 'systemInfo')->name('info');
        Route::get('server-info', 'systemServerInfo')->name('server.info');
        Route::get('optimize', 'optimize')->name('optimize');
        Route::get('optimize-clear', 'optimizeClear')->name('optimize.clear');
        Route::get('composer-install', 'installComposer')->name('composer');
        Route::post('composer-install', 'installComposerProcess')->name('composer.install');
        Route::post('composer-download', 'downloadComposerPhar')->name('composer.download');
        Route::post('composer-manual', 'manualInstall')->name('composer.manual');
        Route::get('direct-download', 'directDownload')->name('composer.direct');
        Route::post('download-package/{package}', 'downloadPackage')->name('composer.package');
        Route::get('run-migrations', 'runMigrations')->name('migrations');
        Route::post('run-migrations', 'runMigrationsProcess')->name('migrations.run');
        Route::get('system-update', 'systemUpdate')->name('update');
        // Update process route disabled - functionality removed
        // Route::post('system-update', 'systemUpdateProcess')->name('update.process');
        // Route::get('system-update/log', 'systemUpdateLog')->name('update.log');
    });

    // Database Migrations Management
    Route::controller('MigrationController')->name('migration.')->prefix('migrations')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('status', 'status')->name('status');
        Route::get('download-sql', 'downloadSql')->name('download.sql');
        Route::post('run', 'run')->name('run');
        Route::post('run/{migrationName}', 'runSpecific')->name('run.specific');
        Route::post('rollback', 'rollback')->name('rollback');
        Route::post('refresh', 'refresh')->name('refresh');
    });


    // SEO
    Route::get('seo', 'FrontendController@seoEdit')->name('seo');


    // Frontend
    Route::name('frontend.')->prefix('frontend')->group(function () {

        Route::controller('FrontendController')->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('templates', 'templates')->name('templates');
            Route::post('templates', 'templatesActive')->name('templates.active');
            Route::get('frontend-sections/{key?}', 'frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'frontendElement')->name('sections.element');
            Route::get('frontend-slug-check/{key}/{id?}', 'frontendElementSlugCheck')->name('sections.element.slug.check');
            Route::get('frontend-element-seo/{key}/{id}', 'frontendSeo')->name('sections.element.seo');
            Route::post('frontend-element-seo/{key}/{id}', 'frontendSeoUpdate');
            Route::post('remove/{id}', 'remove')->name('remove');
        });

        // Page Builder
        Route::controller('PageBuilderController')->group(function () {
            Route::get('manage-pages', 'managePages')->name('manage.pages');
            Route::get('manage-pages/check-slug/{id?}', 'checkSlug')->name('manage.pages.check.slug');
            Route::post('manage-pages', 'managePagesSave')->name('manage.pages.save');
            Route::post('manage-pages/update', 'managePagesUpdate')->name('manage.pages.update');
            Route::post('manage-pages/delete/{id}', 'managePagesDelete')->name('manage.pages.delete');
            Route::get('manage-section/{id}', 'manageSection')->name('manage.section');
            Route::post('manage-section/{id}', 'manageSectionUpdate')->name('manage.section.update');

            Route::get('manage-seo/{id}', 'manageSeo')->name('manage.pages.seo');
            Route::post('manage-seo/{id}', 'manageSeoStore');
        });
    });

    // Marketplace Configuration
    Route::controller('MarketplaceConfigController')->prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('config', 'index')->name('config');
        Route::post('config', 'update')->name('config.update');
        Route::post('config/toggle', 'toggleSetting')->name('config.toggle');
    });

    // Marketplace Homepage Sections
    Route::controller('MarketplaceSettingController')->prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('sections', 'sections')->name('sections');
        Route::post('sections', 'sectionsUpdate')->name('sections.update');
        Route::post('section/update/{id}', 'updateSection')->name('section.update');
        Route::post('section/toggle/{id}', 'toggleSection')->name('section.toggle');
    });
});
