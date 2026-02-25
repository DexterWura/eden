<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    protected $table = 'admin_audit_log';

    protected $fillable = [
        'admin_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'route_name',
        'request_method',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Polymorphic subject (e.g. User, Listing, Escrow).
     */
    public function subject()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }
}
