<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'source_urls',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_path',
        'status',
        'published_at',
        'editorial_reviewed_at',
        'author_id',
        'author_type',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'editorial_reviewed_at' => 'datetime',
        'source_urls' => 'array',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at
            && $this->published_at->isPast();
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'author_id');
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->author_type === 'user') {
            return (string) (\App\Models\User::query()->whereKey($this->author_id)->value('name') ?: 'Eden contributor');
        }
        if ($this->author_type === 'admin') {
            return (string) (\App\Models\Admin::query()->whereKey($this->author_id)->value('name') ?: 'Eden editorial');
        }

        $adminName = \App\Models\Admin::query()->whereKey($this->author_id)->value('name');

        return (string) ($adminName ?: 'Eden editorial');
    }

    public function getPageTitleAttribute(): string
    {
        $siteName = function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden';
        return $this->meta_title ?: ($this->title . ' | ' . $siteName);
    }

    public function getCanonicalUrlAttribute(): string
    {
        return url('/blog/' . $this->slug);
    }

    public function getOgImageUrlAttribute(): ?string
    {
        if (!$this->og_image_path) {
            return null;
        }
        return url(asset($this->og_image_path));
    }

    public function getStructuredDataAttribute(): array
    {
        $siteName = function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden';
        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->title,
            'description' => $this->meta_description ?: $this->excerpt,
            'url' => $this->canonical_url,
            'datePublished' => $this->published_at?->toIso8601String(),
            'dateModified' => $this->updated_at->toIso8601String(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $this->author_name,
            ],
        ];
        $og = $this->og_image_url;
        if ($og) {
            $article['image'] = $og;
        }

        return $article;
    }
}
