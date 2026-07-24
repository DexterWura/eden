<?php

namespace App\Services;

use App\Models\Startup;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class StartupFormService
{
    private const UPLOAD_FIELDS = [
        'logo',
        'founders_names',
        'founders_emails',
        'founders_twitter_urls',
        'founders_linkedin_urls',
        'founders_photo_urls',
        'founders_photos',
        'product_images',
    ];

    public function prepareValidatedData(array $validated, bool $includePhotoUrls = false): array
    {
        $fields = $includePhotoUrls
            ? self::UPLOAD_FIELDS
            : array_values(array_diff(self::UPLOAD_FIELDS, ['founders_photo_urls']));
        foreach ($fields as $field) {
            unset($validated[$field]);
        }

        return $validated;
    }

    public function buildFounders(
        Request $request,
        ?Startup $startup = null,
        ?int $actingUserId = null,
        bool $allowPhotoUrls = false
    ): array {
        $names = $request->input('founders_names', []);
        $emails = $request->input('founders_emails', []);
        $twitterUrls = $request->input('founders_twitter_urls', []);
        $linkedinUrls = $request->input('founders_linkedin_urls', []);
        $photos = $request->file('founders_photos', []);
        $photoUrls = $allowPhotoUrls ? $request->input('founders_photo_urls', []) : [];
        $existing = $startup?->founders ?? [];
        $founders = [];

        foreach ($names as $index => $name) {
            $name = trim((string) ($name ?? ''));
            if ($name === '') {
                continue;
            }
            $photoUrl = $this->resolveFounderPhoto(
                $photos[$index] ?? null,
                $photoUrls[$index] ?? null,
                $existing[$index]['photo_url'] ?? null,
                $index
            );
            $email = trim((string) ($emails[$index] ?? ''));
            $linkedin = trim((string) ($linkedinUrls[$index] ?? ''));
            $founders[] = [
                'name' => $name,
                'photo_url' => $photoUrl,
                'email' => $email !== '' ? $email : null,
                'twitter_url' => $this->normalizeTwitter($twitterUrls[$index] ?? null),
                'linkedin_url' => $linkedin !== '' ? $linkedin : null,
            ];
        }

        return Startup::attachFounderUserIds($founders, $startup, $actingUserId);
    }

    public function buildPublicFounders(array $names, ?int $actingUserId): array
    {
        $founders = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $founders[] = [
                'name' => $name,
                'photo_url' => null,
                'email' => null,
                'twitter_url' => null,
                'linkedin_url' => null,
            ];
        }

        return Startup::attachFounderUserIds($founders, null, $actingUserId);
    }

    public function applyFounderColumns(
        array $data,
        array $founders,
        ?Startup $startup = null,
        ?Authenticatable $fallbackOwner = null
    ): array {
        $first = collect($founders)->first(function ($founder) {
            return trim((string) ($founder['name'] ?? '')) !== '';
        }) ?? [];
        $data['founders'] = $founders;
        $data['founder_name'] = $first['name'] ?? $startup?->founder_name ?? $fallbackOwner?->name;
        $data['founder_email'] = $first['email'] ?? $startup?->founder_email ?? $fallbackOwner?->email;
        $data['founder_twitter_url'] = $first['twitter_url'] ?? $startup?->founder_twitter_url;
        $data['founder_linkedin_url'] = $first['linkedin_url'] ?? $startup?->founder_linkedin_url;
        $data['twitter_url'] = $this->normalizeTwitter($data['twitter_url'] ?? null);

        return $data;
    }

    public function normalizeTwitter(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $lower = strtolower($value);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            return $value;
        }

        return 'https://x.com/' . ltrim($value, '@');
    }

    public function processUploadedFiles(Request $request, Startup $startup): void
    {
        $baseDir = public_path('images/startups/' . $startup->id);
        $updates = [];
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $this->ensureDirectory($baseDir);
            $extension = allowed_image_extension($request->file('logo'));
            $request->file('logo')->move($baseDir, 'logo.' . $extension);
            $updates['logo_path'] = 'images/startups/' . $startup->id . '/logo.' . $extension;
        }

        $productFiles = $request->file('product_images', []);
        if ($productFiles !== []) {
            $productDir = $baseDir . '/products';
            $this->ensureDirectory($productDir);
            $images = $startup->product_images ?? [];
            foreach ($productFiles as $file) {
                if (! $file->isValid()) {
                    continue;
                }
                $extension = allowed_image_extension($file);
                $filename = 'p-' . uniqid() . '.' . $extension;
                $file->move($productDir, $filename);
                $images[] = 'images/startups/' . $startup->id . '/products/' . $filename;
            }
            $updates['product_images'] = $images;
        }

        if ($updates !== []) {
            $startup->update($updates);
        }
    }

    private function resolveFounderPhoto(mixed $photo, mixed $photoUrl, mixed $existingPhoto, int $index): ?string
    {
        if ($photo && $photo->isValid()) {
            $directory = public_path('images/startups/founders');
            $this->ensureDirectory($directory);
            $extension = allowed_image_extension($photo);
            $filename = 'f-' . uniqid() . '-' . $index . '.' . $extension;
            $photo->move($directory, $filename);

            return 'images/startups/founders/' . $filename;
        }
        $candidate = trim((string) $photoUrl);
        if ($candidate !== '' && $this->isSafePhotoUrl($candidate)) {
            return $candidate;
        }

        return trim((string) $existingPhoto) !== '' ? (string) $existingPhoto : null;
    }

    private function isSafePhotoUrl(string $url): bool
    {
        $lower = strtolower($url);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) {
            return false;
        }
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }

        return str_starts_with($url, '/') && ! str_starts_with($url, '//');
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }
    }
}
