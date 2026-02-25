<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Startup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'description',
        'category',
        'website',
        'location',
        'founder_name',
        'launch_date',
        'is_featured',
        'upvotes',
        'twitter_url',
        'linkedin_url',
    ];

    protected $casts = [
        'launch_date' => 'date',
        'is_featured' => 'boolean',
    ];

    public function getLogoLettersAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name), 2);
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }
        return strtoupper(mb_substr($this->name, 0, 2));
    }

    public function getShortDescriptionAttribute(?int $maxLength = null): string
    {
        $maxLength = $maxLength ?? 120;
        if ($this->tagline) {
            return $this->tagline;
        }
        $desc = (string) $this->description;
        if (mb_strlen($desc) <= $maxLength) {
            return $desc;
        }
        return mb_substr($desc, 0, $maxLength - 3) . '...';
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLaunchingToday($query)
    {
        return $query->whereDate('launch_date', today());
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
