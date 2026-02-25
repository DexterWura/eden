<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceFee extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'percent' => 'float',
        'fixed' => 'float',
        'cap' => 'float',
        'is_active' => 'boolean',
    ];
}


