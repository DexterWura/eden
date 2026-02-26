<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Startup extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_BANNED = 'banned';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'tagline',
        'description',
        'category',
        'website',
        'location',
        'founder_name',
        'founder_email',
        'founder_twitter_url',
        'founder_linkedin_url',
        'founders',
        'logo_path',
        'product_images',
        'launch_date',
        'is_featured',
        'upvotes',
        'twitter_url',
        'linkedin_url',
        'status',
    ];

    protected $casts = [
        'launch_date' => 'date',
        'is_featured' => 'boolean',
        'founders' => 'array',
        'product_images' => 'array',
    ];

    public static function founderInitials(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '?';
        }
        $words = preg_split('/\s+/', $name, 2);
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }
        return strtoupper(mb_substr($name, 0, 2));
    }

    public function getLogoLettersAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name), 2);
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }
        return strtoupper(mb_substr($this->name, 0, 2));
    }

    /** @return array<int, array{name: string, photo_url: string|null}> */
    public function getFoundersDisplayAttribute(): array
    {
        $list = $this->founders ?? [];
        if (is_array($list) && count($list) > 0) {
            return array_values(array_map(function ($f) {
                $name = is_array($f) ? ($f['name'] ?? '') : (is_object($f) ? ($f->name ?? '') : '');
                $photo = is_array($f) ? ($f['photo_url'] ?? null) : (is_object($f) ? ($f->photo_url ?? null) : null);
                return ['name' => (string) $name, 'photo_url' => $photo ? (string) $photo : null];
            }, $list));
        }
        if ($this->founder_name) {
            return [['name' => $this->founder_name, 'photo_url' => null]];
        }
        return [];
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

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDisabled($query)
    {
        return $query->where('status', self::STATUS_DISABLED);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', self::STATUS_BANNED);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    public function isBanned(): bool
    {
        return $this->status === self::STATUS_BANNED;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function upvoteRecords()
    {
        return $this->hasMany(StartupUpvote::class);
    }
}
