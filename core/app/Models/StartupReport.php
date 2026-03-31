<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StartupReport extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_DISMISSED = 'dismissed';

    public const REASON_SPAM = 'spam';
    public const REASON_MISLEADING = 'misleading';
    public const REASON_WRONG_CATEGORY = 'wrong_category';
    public const REASON_IMPERSONATION = 'impersonation';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'startup_id',
        'user_id',
        'reporter_email',
        'reason',
        'details',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function startup(): BelongsTo
    {
        return $this->belongsTo(Startup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function reasonLabels(): array
    {
        return [
            self::REASON_SPAM => 'Spam or scam',
            self::REASON_MISLEADING => 'Misleading or inaccurate',
            self::REASON_WRONG_CATEGORY => 'Wrong category',
            self::REASON_IMPERSONATION => 'Impersonation or IP issue',
            self::REASON_OTHER => 'Other',
        ];
    }
}
