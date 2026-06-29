<?php

namespace App\Support;

class TextSanity
{
    /** @var array<int, string> */
    private const SPAM_PHRASES = [
        'payment available',
        'confirm your operation',
        'confirm your payment',
        'verify your account',
        'click here',
        'claim your',
        'you have won',
        'you won',
        'congratulations',
        'crypto wallet',
        'bitcoin',
        'transfer now',
        'act now',
        'limited time offer',
        'free money',
        'cash prize',
        'wire transfer',
        'western union',
        'operation here',
        'hs=',
    ];

    public static function validatePersonName(?string $value): ?string
    {
        return self::validateLabel($value, minLength: 2, maxLength: 80, minLetters: 2, maxWords: 8, personName: true);
    }

    public static function validateDisplayName(?string $value): ?string
    {
        return self::validateLabel($value, minLength: 2, maxLength: 120, minLetters: 2, maxWords: 12, personName: false);
    }

    public static function validateShortText(?string $value, int $maxLength = 255): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = self::normalize($value);
        if (mb_strlen($normalized) > $maxLength) {
            return 'This field is too long.';
        }

        return self::commonSpamChecks($normalized, strict: false);
    }

    public static function validateCommentBody(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return 'This field is required.';
        }

        $normalized = self::normalize($value);
        if (mb_strlen($normalized) > 2000) {
            return 'This field is too long.';
        }

        return self::commonSpamChecks($normalized, strict: true);
    }

    /**
     * Best-effort cleanup for OAuth-provided names that fail validation.
     */
    public static function fallbackPersonName(?string $value, ?string $email = null): string
    {
        $normalized = self::normalize($value ?? '');
        if ($normalized !== '' && self::validatePersonName($normalized) === null) {
            return $normalized;
        }

        if ($email !== null && str_contains($email, '@')) {
            $local = trim(strtok($email, '@') ?: '');
            $local = preg_replace('/[^a-zA-Z0-9._-]+/', ' ', $local) ?? '';
            $local = self::normalize($local);
            if ($local !== '' && self::validatePersonName($local) === null) {
                return $local;
            }
        }

        return 'User';
    }

    private static function validateLabel(
        ?string $value,
        int $minLength,
        int $maxLength,
        int $minLetters,
        int $maxWords,
        bool $personName,
    ): ?string {
        if ($value === null || trim($value) === '') {
            return 'This field is required.';
        }

        $normalized = self::normalize($value);
        $length = mb_strlen($normalized);

        if ($length < $minLength) {
            return 'Please enter at least ' . $minLength . ' characters.';
        }

        if ($length > $maxLength) {
            return 'Please keep this under ' . $maxLength . ' characters.';
        }

        if (preg_match_all('/\p{L}/u', $normalized) < $minLetters) {
            return 'Please enter a real name using letters.';
        }

        $wordCount = count(array_filter(preg_split('/\s+/u', $normalized) ?: []));
        if ($wordCount > $maxWords) {
            return 'Please use a shorter name.';
        }

        if ($personName && preg_match('/\$[\d,]/', $normalized)) {
            return 'Names cannot contain payment amounts or promotional text.';
        }

        if ($personName && preg_match('/\d{3,}/', $normalized)) {
            return 'Names cannot contain long number sequences.';
        }

        if ($personName && substr_count($normalized, '*') >= 2) {
            return 'Please enter a real name without special formatting.';
        }

        if ($personName && preg_match('/[^\p{L}\p{M}\s\'\-\.]/u', $normalized)) {
            return 'Names may only contain letters, spaces, hyphens, apostrophes, and periods.';
        }

        return self::commonSpamChecks($normalized, strict: true);
    }

    private static function commonSpamChecks(string $value, bool $strict): ?string
    {
        $lower = mb_strtolower($value);

        if (self::containsUrl($value)) {
            return 'Links and URLs are not allowed here.';
        }

        foreach (self::SPAM_PHRASES as $phrase) {
            if (str_contains($lower, $phrase)) {
                return 'This text looks like spam. Please use a normal name or message.';
            }
        }

        if ($strict && preg_match('/(\*\s*){2,}/', $value)) {
            return 'Please remove decorative asterisks or promotional formatting.';
        }

        if ($strict && preg_match('/\b(?:hs|ref|token|code)=[\w-]{8,}/i', $value)) {
            return 'This text looks like spam.';
        }

        if ($strict && preg_match('/[!?]{3,}/', $value)) {
            return 'Please reduce repeated punctuation.';
        }

        return null;
    }

    private static function containsUrl(string $value): bool
    {
        if (preg_match('#https?://#i', $value)) {
            return true;
        }

        if (preg_match('#\bwww\.[\w.-]+\.[a-z]{2,}(?:/\S*)?#i', $value)) {
            return true;
        }

        return (bool) preg_match(
            '~\b[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.(?:com|net|org|io|co|app|dev|info|biz|xyz|me|us|uk|school|edu|gov|ly|link|site|online|shop|store|click|top|vip|work|live|pro|tech|cloud|ai)(?:[/?#]\S*)?~i',
            $value
        );
    }

    private static function normalize(string $value): string
    {
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return $value;
    }
}
