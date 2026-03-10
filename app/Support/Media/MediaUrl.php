<?php

namespace App\Support\Media;

final class MediaUrl
{
    /**
     * Convert a stored media key into a public URL.
     * - Absolute URLs are returned as-is.
     * - Leading-slash paths are returned as-is.
     * - Bare keys are prefixed with APP_MEDIA_CDN_BASE_URL.
     */
    public static function fromKeyOrUrl(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://') || str_starts_with($raw, '/')) {
            return $raw;
        }

        $base = rtrim((string) config('app.cdn_base_url', ''), '/');
        if ($base === '') {
            return '/'.ltrim($raw, '/');
        }

        return $base.'/'.ltrim($raw, '/');
    }
}
