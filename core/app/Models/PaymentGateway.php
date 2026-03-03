<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = ['name', 'alias', 'enabled', 'parameters'];

    protected $casts = [
        'enabled' => 'boolean',
        'parameters' => 'array',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function param(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }
}
