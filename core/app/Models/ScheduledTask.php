<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'interval_minutes',
        'is_enabled',
        'last_run_at',
        'last_status',
        'last_message',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function isDue(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }
        if ($this->last_run_at === null) {
            return true;
        }
        return $this->last_run_at->addMinutes($this->interval_minutes)->isPast();
    }

    public function markSuccess(?string $message = null): void
    {
        $this->update([
            'last_run_at' => now(),
            'last_status' => self::STATUS_SUCCESS,
            'last_message' => $message,
        ]);
    }

    public function markFailed(?string $message = null): void
    {
        $this->update([
            'last_run_at' => now(),
            'last_status' => self::STATUS_FAILED,
            'last_message' => $message,
        ]);
    }
}
