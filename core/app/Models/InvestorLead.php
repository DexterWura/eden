<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorLead extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUSES = [self::STATUS_NEW, self::STATUS_CONTACTED, self::STATUS_ARCHIVED];

    protected $fillable = [
        'startup_funding_round_id',
        'name',
        'email',
        'organization',
        'message',
        'notes',
        'status',
        'contacted_at',
        'archived_at',
        'ip_hash',
    ];

    protected $hidden = ['ip_hash'];

    protected $casts = [
        'contacted_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function fundingRound()
    {
        return $this->belongsTo(StartupFundingRound::class, 'startup_funding_round_id');
    }

    public function transitionTo(string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid investor lead status.');
        }

        $this->update([
            'status' => $status,
            'contacted_at' => $status === self::STATUS_CONTACTED ? now() : $this->contacted_at,
            'archived_at' => $status === self::STATUS_ARCHIVED ? now() : null,
        ]);
    }
}
