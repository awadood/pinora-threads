<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMeta
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'owner');
    }

    /**
     * Returns resolved SEO with deterministic fallbacks (LOCKED).
     */
    public function seoResolved(array $ctx = []): array
    {
        $seo = $this->seo; // eager load in queries

        // Entity defaults (derive from model fields if present)
        $baseTitle = $ctx['base_title'] ?? ($this->meta_title ?? $this->name ?? null);
        $baseDesc  = $ctx['base_description'] ?? ($this->meta_description ?? null);
        $baseUrl   = $ctx['canonical_url'] ?? ($ctx['url'] ?? null);

        // 1) Core
        $metaTitle = $seo?->meta_title ?: $baseTitle;
        $metaDesc  = $seo?->meta_description ?: $baseDesc;

        // Default robots: index/follow unless explicitly overridden
        $robots = $seo?->meta_robots ?: 'index,follow';

        // Canonical: allow override; otherwise use computed
        $canonical = $seo?->canonical_url ?: $baseUrl;

        // 2) Open Graph fallbacks
        $ogTitle = $seo?->og_title ?: $metaTitle;
        $ogDesc  = $seo?->og_description ?: $metaDesc;
        $ogType  = $seo?->og_type ?: ($ctx['og_type'] ?? 'website');
        $ogUrl   = $seo?->og_url ?: $canonical;

        // OG image fallback strategy:
        // - explicit og_image_id in seo_meta
        // - else: media_attachments role 'og_image' or 'hero' or 'thumbnail' (if you implement helper)
        $ogImageId = $seo?->og_image_id ?? ($ctx['og_image_id'] ?? null);

        // 3) Twitter fallbacks
        $twCard = $seo?->twitter_card ?: 'summary_large_image';
        $twTitle = $seo?->twitter_title ?: $ogTitle;
        $twDesc  = $seo?->twitter_description ?: $ogDesc;
        $twImageId = $seo?->twitter_image_id ?? $ogImageId;

        // 4) Schema fallbacks
        $schemaType = $seo?->schema_type ?: ($ctx['schema_type'] ?? null);
        $schemaPayload = $seo?->schema_payload ?? ($ctx['schema_payload'] ?? null);

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDesc,
            'meta_robots' => $robots,
            'canonical_url' => $canonical,

            'og' => [
                'title' => $ogTitle,
                'description' => $ogDesc,
                'type' => $ogType,
                'url' => $ogUrl,
                'image_id' => $ogImageId,
            ],

            'twitter' => [
                'card' => $twCard,
                'title' => $twTitle,
                'description' => $twDesc,
                'image_id' => $twImageId,
            ],

            'schema' => [
                'type' => $schemaType,
                'payload' => $schemaPayload,
            ],
        ];
    }
}
