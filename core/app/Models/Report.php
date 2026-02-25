<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_REVIEWED = 1;

    const REASON_SPAM = 'spam';
    const REASON_MISLEADING = 'misleading';
    const REASON_SCAM = 'scam';
    const REASON_INAPPROPRIATE = 'inappropriate';
    const REASON_OTHER = 'other';

    protected $guarded = ['id'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public static function reasonOptions(): array
    {
        return [
            self::REASON_SPAM => __('Spam'),
            self::REASON_MISLEADING => __('Misleading information'),
            self::REASON_SCAM => __('Potential scam'),
            self::REASON_INAPPROPRIATE => __('Inappropriate content'),
            self::REASON_OTHER => __('Other'),
        ];
    }
}
