<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Startup extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_BANNED = 'banned';
    public const STATUS_DORMANT = 'dormant';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'tagline',
        'description',
        'category',
        'website',
        'website_last_checked_at',
        'website_is_reachable',
        'website_consecutive_failures',
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
        'product_of_day_at',
        'views',
        'clicks',
        'traffic_tracking_enabled',
        'mrr',
        'revenue',
        'twitter_url',
        'linkedin_url',
        'status',
        'dormant_at',
        'search_console_property',
        'for_sale',
        'flipit_listing_id',
        'sold_at',
        'featured_on_hero',
        'hero_request_status',
    ];

    /** FLIPit listing URL pattern: https://flipit.co.zw/marketplace/listing/{id} */
    public const FLIPIT_LISTING_URL_PATTERN = '#^https?://(?:www\.)?flipit\.co\.zw/marketplace/listing/([a-zA-Z0-9_-]+)/?$#i';

    /** FLIPit listing number format: 12 alphanumeric (A-Z, 1-9) from dashboard */
    public const FLIPIT_LISTING_NUMBER_PATTERN = '/^[A-Za-z0-9]{12}$/';

    protected $casts = [
        'launch_date' => 'date',
        'product_of_day_at' => 'date',
        'dormant_at' => 'datetime',
        'website_last_checked_at' => 'datetime',
        'website_is_reachable' => 'boolean',
        'sold_at' => 'datetime',
        'is_featured' => 'boolean',
        'featured_on_hero' => 'boolean',
        'for_sale' => 'boolean',
        'traffic_tracking_enabled' => 'boolean',
        'founders' => 'array',
        'product_images' => 'array',
        'mrr' => 'decimal:2',
        'revenue' => 'decimal:2',
    ];

    /**
     * Extract FLIPit listing ID from a marketplace listing URL.
     * Example: https://flipit.co.zw/marketplace/listing/edencozw-JRCFDcJE -> edencozw-JRCFDcJE
     */
    public static function flipitListingIdFromUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }
        $url = trim($url);
        if (preg_match(self::FLIPIT_LISTING_URL_PATTERN, $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Whether the stored flipit_listing_id is a listing number (12 alphanumeric) rather than a slug.
     */
    public static function isFlipitListingNumber(?string $value): bool
    {
        return $value !== null && $value !== '' && preg_match(self::FLIPIT_LISTING_NUMBER_PATTERN, trim($value));
    }

    /**
     * Canonical FLIPit marketplace URL for this startup's listing (by-number or by slug).
     */
    public function getFlipitListingUrl(): ?string
    {
        $id = $this->flipit_listing_id;
        if ($id === null || $id === '') {
            return null;
        }
        $id = trim($id);
        if (self::isFlipitListingNumber($id)) {
            return 'https://flipit.co.zw/marketplace/listing/by-number/' . $id;
        }
        return 'https://flipit.co.zw/marketplace/listing/' . $id;
    }

    public function scopeForSale($query)
    {
        return $query->where('for_sale', true)->whereNull('sold_at');
    }

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

    /** @return array<int, array{name: string, photo_url: string|null, email: string|null, twitter_url: string|null, linkedin_url: string|null}> */
    public function getFoundersDisplayAttribute(): array
    {
        $list = $this->founders ?? [];
        if (is_array($list) && count($list) > 0) {
            return array_values(array_map(function ($f) {
                $name = is_array($f) ? ($f['name'] ?? '') : (is_object($f) ? ($f->name ?? '') : '');
                $photo = is_array($f) ? ($f['photo_url'] ?? null) : (is_object($f) ? ($f->photo_url ?? null) : null);
                $email = is_array($f) ? ($f['email'] ?? null) : (is_object($f) ? ($f->email ?? null) : null);
                $twitter = is_array($f) ? ($f['twitter_url'] ?? null) : (is_object($f) ? ($f->twitter_url ?? null) : null);
                $linkedin = is_array($f) ? ($f['linkedin_url'] ?? null) : (is_object($f) ? ($f->linkedin_url ?? null) : null);
                return [
                    'name' => (string) $name,
                    'photo_url' => $photo ? (string) $photo : null,
                    'email' => $email ? (string) $email : null,
                    'twitter_url' => $twitter ? (string) $twitter : null,
                    'linkedin_url' => $linkedin ? (string) $linkedin : null,
                ];
            }, $list));
        }
        if ($this->founder_name) {
            return [[
                'name' => $this->founder_name,
                'photo_url' => null,
                'email' => $this->founder_email ? (string) $this->founder_email : null,
                'twitter_url' => $this->founder_twitter_url ? (string) $this->founder_twitter_url : null,
                'linkedin_url' => $this->founder_linkedin_url ? (string) $this->founder_linkedin_url : null,
            ]];
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

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
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

    public function scopeDormant($query)
    {
        return $query->where('status', self::STATUS_DORMANT);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function hasFounderWithLinkedin(): bool
    {
        foreach ($this->founders_display as $f) {
            if (trim($f['linkedin_url'] ?? '') !== '') {
                return true;
            }
        }
        return false;
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

    public function isDormant(): bool
    {
        return $this->status === self::STATUS_DORMANT;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the given user can manage this startup (owner or listed founder by email).
     */
    public function userCanManage(?\Illuminate\Contracts\Auth\Authenticatable $user): bool
    {
        if ($user === null) {
            return false;
        }
        if ($this->user_id === $user->getAuthIdentifier()) {
            return true;
        }
        $userEmail = is_string($user->email ?? null) ? strtolower(trim($user->email)) : '';
        if ($userEmail === '') {
            return false;
        }
        $founders = $this->founders ?? [];
        foreach ($founders as $f) {
            $email = is_array($f) ? ($f['email'] ?? '') : (is_object($f) ? ($f->email ?? '') : '');
            if (is_string($email) && strtolower(trim($email)) === $userEmail) {
                return true;
            }
        }
        return false;
    }

    /**
     * Scope: startups the user can manage (owner or listed as founder by email).
     */
    public function scopeVisibleToUser($query, \App\Models\User $user)
    {
        $userId = $user->getAuthIdentifier();
        $email = $user->email ?? '';

        return $query->where(function ($q) use ($userId, $email) {
            $q->where('user_id', $userId);
            if ($email !== '') {
                $q->orWhereRaw(
                    "founders IS NOT NULL AND JSON_SEARCH(founders, 'one', ?, NULL, '$[*].email') IS NOT NULL",
                    [$email]
                );
            }
        });
    }

    public function upvoteRecords()
    {
        return $this->hasMany(StartupUpvote::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_startups')->withTimestamps();
    }

    public function launchNotifications()
    {
        return $this->hasMany(LaunchNotification::class);
    }

    public function comments()
    {
        return $this->hasMany(StartupComment::class)->orderBy('created_at', 'asc');
    }

    public function reports()
    {
        return $this->hasMany(StartupReport::class);
    }

    public function fundingRounds()
    {
        return $this->hasMany(StartupFundingRound::class)->orderByDesc('created_at');
    }

    public function activeFundingRound()
    {
        return $this->hasOne(StartupFundingRound::class)->where('status', StartupFundingRound::STATUS_OPEN)->latest();
    }

    public function trafficDaily()
    {
        return $this->hasMany(StartupTrafficDaily::class)->orderBy('date');
    }

    /**
     * Normalize a listing title for duplicate checks (case and spacing only).
     */
    public static function normalizeListingName(?string $name): ?string
    {
        if ($name === null || trim($name) === '') {
            return null;
        }
        $n = trim(preg_replace('/\s+/u', ' ', $name));

        return mb_strtolower($n, 'UTF-8');
    }

    public static function listingNameExistsForAnother(?string $name, ?int $excludeId = null): bool
    {
        $norm = self::normalizeListingName($name);
        if ($norm === null || $norm === '') {
            return false;
        }

        return self::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get(['id', 'name'])
            ->contains(fn ($s) => self::normalizeListingName($s->name) === $norm);
    }

    public static function normalizeUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }
        $url = trim($url);
        $url = preg_replace('#^https?://#i', '', $url);
        $url = preg_replace('#^www\.#i', '', $url);
        $url = rtrim($url, '/');
        return strtolower($url);
    }

    public static function websiteExistsForAnother(?string $website, ?int $excludeId = null): bool
    {
        if ($website === null || trim($website) === '') {
            return false;
        }
        $normalized = self::normalizeUrl($website);
        if ($normalized === null || $normalized === '') {
            return false;
        }
        return self::query()
            ->whereNotNull('website')
            ->where('website', '!=', '')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get(['id', 'website'])
            ->contains(fn ($s) => self::normalizeUrl($s->website) === $normalized);
    }
}
