<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon_path'];

    public function startups(): HasMany
    {
        return $this->hasMany(Startup::class);
    }
}
