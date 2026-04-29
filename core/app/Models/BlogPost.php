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
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_path',
        'status',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
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
        ];
        $og = $this->og_image_url;
        if ($og) {
            $article['image'] = $og;
        }

        return $article;
    }
}
