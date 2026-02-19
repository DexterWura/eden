<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Startup extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'name', 'slug', 'description', 'url', 'founder', 'founder_socials', 'startup_socials', 'tags',
        'mrr', 'arr', 'is_for_sale', 'status', 'is_featured',
        'submitted_at', 'approved_at', 'last_updated_at',
        'url_failure_count', 'last_url_failure_at', 'view_count',
    ];

    protected function casts(): array
    {
        return [
            'founder_socials' => 'array',
            'startup_socials' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'last_updated_at' => 'datetime',
            'last_url_failure_at' => 'datetime',
            'is_for_sale' => 'boolean',
            'is_featured' => 'boolean',
            'mrr' => 'decimal:2',
            'arr' => 'decimal:2',
            'url_failure_count' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public const STATUS_SEEDLING = 'seedling';
    public const STATUS_SAPLING = 'sapling';
    public const STATUS_FLOURISHING = 'flourishing';
    public const STATUS_WILTED = 'wilted';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function growthLogs(): HasMany
    {
        return $this->hasMany(GrowthLog::class);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getVotesCountAttribute(): int
    {
        return $this->votes()->count();
    }

    public function isClaimed(): bool
    {
        return $this->user_id !== null;
    }
}
