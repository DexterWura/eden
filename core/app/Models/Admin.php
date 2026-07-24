<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $attributes = [
        'is_super_admin' => false,
        'status' => self::STATUS_ENABLED,
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'image',
        'is_super_admin',
        'allowed_modules',
        'status',
        'last_saw_contact_messages_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
        'allowed_modules' => 'array',
        'status' => 'integer',
        'last_saw_contact_messages_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * Check if admin is super admin (full access).
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Check if admin has access to the given module.
     */
    public function hasModule(string $module): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $modules = $this->allowed_modules ?? [];
        return is_array($modules) && in_array($module, $modules, true);
    }

    /**
     * Get list of allowed module keys (empty = all for super admin).
     */
    public function getAllowedModules(): array
    {
        if ($this->isSuperAdmin()) {
            return config('admin_modules.modules', []);
        }
        $modules = $this->allowed_modules ?? [];
        return is_array($modules) ? $modules : [];
    }

    /**
     * Check if admin account is enabled.
     */
    public function isEnabled(): bool
    {
        return (int) $this->status === self::STATUS_ENABLED;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Audit log entries created by this admin.
     */
    public function auditLogs()
    {
        return $this->hasMany(AdminAuditLog::class, 'admin_id');
    }

    /**
     * Check if this admin is the demo/test admin user.
     * Demo user has read-only access and sensitive data is masked.
     *
     * @return bool
     */
    public function isDemoUser(): bool
    {
        return $this->username === 'demoadmin';
    }
}
