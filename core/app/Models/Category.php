<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'introduction',
        'market_context',
        'frequently_asked_questions',
        'sort_order',
    ];

    protected $casts = [
        'frequently_asked_questions' => 'array',
    ];

    public function startups()
    {
        return $this->hasMany(Startup::class, 'category', 'name');
    }

    public function hasEditorialDepth(): bool
    {
        $introductionLength = mb_strlen(trim((string) $this->introduction));
        $contextLength = mb_strlen(trim((string) $this->market_context));

        return $introductionLength >= 200 && $contextLength >= 120;
    }
}
