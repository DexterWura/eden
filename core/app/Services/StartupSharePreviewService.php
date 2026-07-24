<?php

namespace App\Services;

use App\Models\Startup;
use Illuminate\Support\Str;

class StartupSharePreviewService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Startup $startup): array
    {
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $canonicalUrl = route('startup.show', $startup->slug);
        $description = $this->description($startup);
        $imageUrl = $this->imageUrl($startup);
        $title = $startup->name;
        if ($startup->tagline) {
            $title .= ' | ' . Str::limit($startup->tagline, 50);
        } elseif ($startup->category) {
            $title .= ' | ' . $startup->category;
        }
        $title .= ' | ' . $siteName;

        $shareText = trim($startup->name . ($startup->tagline ? ' — ' . $startup->tagline : ''));

        return [
            'pageTitle' => $title,
            'metaDescription' => $description,
            'metaImage' => $imageUrl,
            'canonicalUrl' => $canonicalUrl,
            'metaKeywords' => implode(', ', array_filter([
                $startup->name,
                $startup->category,
                $startup->location,
                $siteName . ' app directory',
            ])),
            'structuredData' => $this->structuredData($startup, $canonicalUrl, $imageUrl),
            'ogImageAlt' => $startup->name,
            'includeDefaultSiteGraph' => false,
            'metaRobots' => $startup->shouldBeIndexed() ? null : 'noindex,follow',
            'shareText' => $shareText,
            'shareUrl' => $canonicalUrl,
            'xShareUrl' => 'https://twitter.com/intent/tweet?' . http_build_query([
                'url' => $canonicalUrl,
                'text' => $shareText,
            ]),
            'linkedInShareUrl' => 'https://www.linkedin.com/sharing/share-offsite/?' . http_build_query([
                'url' => $canonicalUrl,
            ]),
        ];
    }

    public function description(Startup $startup, int $maxLength = 160): string
    {
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $raw = $startup->description ?: $startup->tagline ?: $startup->name . ' – app listed on ' . $siteName;
        $text = strip_tags(preg_replace('/\s+/', ' ', (string) $raw));

        return mb_strlen($text) <= $maxLength
            ? $text
            : mb_substr($text, 0, $maxLength - 3) . '...';
    }

    private function imageUrl(Startup $startup): ?string
    {
        if ($startup->logo_path) {
            return url()->asset($startup->logo_path);
        }
        $productImages = $startup->product_images ?? [];

        return is_string($productImages[0] ?? null) ? url()->asset($productImages[0]) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function structuredData(Startup $startup, string $url, ?string $imageUrl): array
    {
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $startup->name,
            'url' => $startup->website ?: $url,
            'description' => $this->description($startup, 500),
            'publisher' => ['@type' => 'Organization', 'name' => $siteName, 'url' => url('/')],
        ];
        if ($imageUrl) {
            $organization['image'] = $imageUrl;
        }
        if ($startup->location) {
            $organization['address'] = ['@type' => 'PostalAddress', 'addressLocality' => $startup->location];
        }
        $sameAs = array_values(array_filter([$startup->website, $startup->twitter_url, $startup->linkedin_url]));
        if ($sameAs !== []) {
            $organization['sameAs'] = $sameAs;
        }
        $members = collect($startup->founders_display)
            ->filter(fn (array $founder) => trim($founder['name'] ?? '') !== '')
            ->map(function (array $founder): array {
                $member = ['@type' => 'Person', 'name' => $founder['name']];
                if ($founder['linkedin_url']) {
                    $member['url'] = $founder['linkedin_url'];
                }

                return $member;
            })
            ->values()
            ->all();
        if ($members !== []) {
            $organization['member'] = $members;
        }

        $schemas = [$organization];
        if (in_array($startup->category, ['SaaS', 'Developer Tools', 'Artificial Intelligence', 'Mobile Apps', 'Productivity'], true)) {
            $application = [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => $startup->name,
                'description' => $this->description($startup, 500),
                'url' => $url,
                'applicationCategory' => $startup->category,
            ];
            if ($imageUrl) {
                $application['image'] = $imageUrl;
            }
            if ($startup->website) {
                $application['offers'] = ['@type' => 'Offer', 'url' => $startup->website];
            }
            $schemas[] = $application;
        }

        return $schemas;
    }
}
