<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProPayment extends Model
{
    protected $fillable = [
        'user_id', 'gateway', 'trx', 'amount', 'currency', 'status', 'gateway_response',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
