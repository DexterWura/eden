<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\SettingService::class, function ($app) {
            return new \App\Services\SettingService;
        });
    }

    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.default');
        \Illuminate\Support\Facades\Gate::define('blog', fn ($user) => $user->hasBloggingAccess());
        $this->applyMailConfigFromSettings();
    }

    protected function applyMailConfigFromSettings(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        $s = $this->app->make(\App\Services\SettingService::class);
        $driver = $s->get('mail_driver');
        if ($driver === null || $driver === '') {
            return;
        }

        $mailer = $driver === 'php' ? 'sendmail' : 'smtp';
        config(['mail.default' => $mailer]);
        config(['mail.from' => [
            'address' => $s->get('mail_from_address') ?: config('mail.from.address'),
            'name' => $s->get('mail_from_name') ?: config('mail.from.name'),
        ]]);

        if ($mailer === 'smtp') {
            config(['mail.mailers.smtp' => array_merge(config('mail.mailers.smtp', []), [
                'host' => $s->get('mail_host') ?: config('mail.mailers.smtp.host'),
                'port' => (int) ($s->get('mail_port') ?: config('mail.mailers.smtp.port', 587)),
                'username' => $s->get('mail_username'),
                'password' => $s->get('mail_password'),
                'encryption' => $s->get('mail_encryption') ?: null,
            ])]);
        }
    }
}
