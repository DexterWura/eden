<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingCategory extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function parent()
    {
        return $this->belongsTo(ListingCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ListingCategory::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByBusinessType($query, $type)
    {
        return $query->where('business_type', $type);
    }

    public function scopeParentCategories($query)
    {
        return $query->where('parent_id', 0);
    }
}

