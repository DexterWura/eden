<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingImage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}

