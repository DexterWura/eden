<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order'];

    public function startups()
    {
        return $this->hasMany(Startup::class, 'category', 'name');
    }
}
