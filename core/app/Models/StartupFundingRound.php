<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StartupFundingRound extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public const ROUND_TYPES = [
        'pre_seed' => 'Pre-seed',
        'seed' => 'Seed',
        'series_a' => 'Series A',
        'series_b' => 'Series B',
        'series_c' => 'Series C',
        'bridge' => 'Bridge',
        'other' => 'Other / Looking for investors',
    ];

    protected $fillable = [
        'startup_id',
        'round_type',
        'amount_seeking',
        'currency',
        'description',
        'contact_email',
        'status',
        'opportunity_announced_at',
    ];

    protected $casts = [
        'amount_seeking' => 'decimal:2',
        'opportunity_announced_at' => 'datetime',
    ];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }

    public function investorLeads()
    {
        return $this->hasMany(InvestorLead::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function getRoundTypeLabelAttribute(): string
    {
        return self::ROUND_TYPES[$this->round_type] ?? ucfirst(str_replace('_', ' ', $this->round_type));
    }
}
