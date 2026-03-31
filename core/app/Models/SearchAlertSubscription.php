<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SearchAlertSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'search_query',
        'category',
        'location',
        'criteria_hash',
        'unsubscribe_token',
        'last_notified_at',
    ];

    protected $casts = [
        'last_notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function hashCriteria(?string $searchQuery, ?string $category, ?string $location): string
    {
        $parts = [
            $searchQuery !== null && trim($searchQuery) !== '' ? trim($searchQuery) : '',
            $category !== null && $category !== '' ? $category : '',
            $location !== null && trim((string) $location) !== '' ? trim((string) $location) : '',
        ];

        return hash('sha256', implode("\0", $parts));
    }

    protected static function booted(): void
    {
        static::creating(function (SearchAlertSubscription $model): void {
            if ($model->criteria_hash === null || $model->criteria_hash === '') {
                $model->criteria_hash = self::hashCriteria(
                    $model->search_query,
                    $model->category,
                    $model->location
                );
            }
            if ($model->unsubscribe_token === null || $model->unsubscribe_token === '') {
                $model->unsubscribe_token = Str::random(48);
            }
        });
    }

    public function summaryLabel(): string
    {
        $bits = array_filter([
            $this->search_query ? '“' . $this->search_query . '”' : null,
            $this->category ?: null,
            $this->location ?: null,
        ]);

        return $bits !== [] ? implode(' · ', $bits) : 'your saved filters';
    }
}
