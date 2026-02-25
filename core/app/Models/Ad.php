<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Ad extends Model
{
    protected $fillable = [
        'slot', 'type', 'name', 'width', 'height', 'content',
        'adsense_client', 'adsense_slot', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public const SLOT_ABOVE_FOLD = 'above_fold';
    public const SLOT_IN_FEED = 'in_feed';
    public const SLOT_SIDEBAR = 'sidebar';
    public const SLOT_IN_CONTENT = 'in_content';

    public const TYPE_ADSENSE = 'adsense';
    public const TYPE_ZIMADSENSE = 'zimadsense';
    public const TYPE_CUSTOM = 'custom';

    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && Carbon::now()->isAfter($this->expires_at)) {
            return false;
        }
        return true;
    }
}
