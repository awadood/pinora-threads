<?php

namespace App\Repositories\Catalog\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * ProductFilters
 *
 * Parses and normalizes /api/products query params into a structured filter DTO input.
 * Enforces a strict allow-list per profile.
 *
 * PROFILES
 * 1) storefront (public PLP)
 *    Filters:
 *      - q (top-level)
 *      - filter[category.slug.eq]
 *      - filter[collection.slug.eq]
 *      - filter[attr.<code>.eq|in]
 *      - filter[price.gte|lte]            (ctx currency, any variant)
 *      - filter[in_stock.eq]=1           (purchasable: stock_levels.quantity > 0, any stock in country)
 *    Sort:
 *      - newest | name | -name | price | -price
 *    Notes:
 *      - Always one row per product.
 *      - selected_variant is returned by storefront response layer.
 *
 * 2) admin (authenticated product index)
 *    Filters (superset of storefront):
 *      - all storefront filters (q, category/collection, attr, price, in_stock)
 *      - filter[active.eq]=1|0
 *      - filter[type.eq|in]=...
 *      - filter[sku.eq], filter[slug.eq], filter[name.like]
 *      - filter[publishable.eq]=1|0      (default variant: thumbnail exists AND gallery_count >= 7)
 *    Sort:
 *      - name | -name | updated_at | -updated_at | active | -active | type | -type
 *    Notes:
 *      - Admin response omits selected_variant by default.
 */
final class ProductFilters
{
    public const PROFILE_STOREFRONT = 'storefront';

    public const PROFILE_ADMIN = 'admin';

    /**
     * Storefront allowed sort tokens.
     */
    private const STOREFRONT_SORTS = [
        'newest', 'name', '-name', 'price', '-price',
    ];

    /**
     * Admin allowed sort tokens.
     */
    private const ADMIN_SORTS = [
        'name', '-name',
        'updated_at', '-updated_at',
        'active', '-active',
        'type', '-type',
    ];

    /**
     * Parse request into normalized filters.
     *
     * @return array{
     *   profile:string,
     *   sort:string,
     *   raw_filters:array,
     *   echo:array,
     *   has_variant_constraints:bool,
     *   product:array,
     *   variant:array
     * }
     */
    public function parse(Request $request, string $profile): array
    {
        $profile = $this->normalizeProfile($profile);

        $rawFilters = $request->query('filter', []);
        $rawFilters = is_array($rawFilters) ? $rawFilters : [];

        // q is intentionally top-level (storefront convention), but admin can also use it.
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : null;
        if ($q === '') {
            $q = null;
        }

        $sort = (string) $request->query('sort', $profile === self::PROFILE_ADMIN ? '-updated_at' : 'newest');
        $sort = $this->normalizeSort($sort, $profile);

        // ---------- Product-level filters ----------
        $product = [
            'active' => $profile === self::PROFILE_ADMIN ? $this->getBoolish($rawFilters, 'active.eq') : null,
            'type' => $profile === self::PROFILE_ADMIN ? $this->getScalar($rawFilters, 'type.eq') : null,
            'type_in' => $profile === self::PROFILE_ADMIN ? $this->getCsv($rawFilters, 'type.in') : null,

            'sku' => $profile === self::PROFILE_ADMIN ? $this->getScalar($rawFilters, 'sku.eq') : null,
            'slug' => $profile === self::PROFILE_ADMIN ? $this->getScalar($rawFilters, 'slug.eq') : null,
            'name' => $profile === self::PROFILE_ADMIN ? $this->getScalar($rawFilters, 'name.like') : null,

            'category_slug' => $this->getScalar($rawFilters, 'category.slug.eq'),
            'collection_slug' => $this->getScalar($rawFilters, 'collection.slug.eq'),

            // publishability is admin-only (default variant: thumbnail exists + gallery >= 7)
            'publishable' => $profile === self::PROFILE_ADMIN ? $this->getBoolish($rawFilters, 'publishable.eq') : null,
        ];

        // ---------- Variant-level filters (match ANY variant) ----------
        $variant = [
            'q' => $q,

            // attributes: attr.<code>.eq / attr.<code>.in
            'attrs_eq' => $this->getAttrEqFilters($rawFilters),
            'attrs_in' => $this->getAttrInFilters($rawFilters),

            // price range (ctx currency, any variant)
            'price_gte' => $this->getNumeric($rawFilters, 'price.gte'),
            'price_lte' => $this->getNumeric($rawFilters, 'price.lte'),

            // in stock / purchasable: stock_levels.quantity > 0 (any stock in ctx country)
            'in_stock' => $this->getBoolish($rawFilters, 'in_stock.eq'),
        ];

        // Enforce allow-lists:
        // - storefront ignores admin-only keys by leaving them null
        // - admin can use both sets (superset)

        $hasVariantConstraints =
            ($variant['q'] !== null)
            || (! empty($variant['attrs_eq']))
            || (! empty($variant['attrs_in']))
            || ($variant['price_gte'] !== null)
            || ($variant['price_lte'] !== null)
            || ($variant['in_stock'] === true);

        // Echo: return what was actually applied (normalized, not raw)
        $echo = [
            'q' => $variant['q'],
            'sort' => $sort,
            'filter' => array_filter([
                'category.slug.eq' => $product['category_slug'],
                'collection.slug.eq' => $product['collection_slug'],

                'price.gte' => $variant['price_gte'],
                'price.lte' => $variant['price_lte'],
                'in_stock.eq' => $variant['in_stock'] ? 1 : null,

                // attributes
                'attr.eq' => $variant['attrs_eq'],
                'attr.in' => $variant['attrs_in'],

                // admin-only
                'active.eq' => $profile === self::PROFILE_ADMIN ? ($product['active'] === null ? null : ($product['active'] ? 1 : 0)) : null,
                'type.eq' => $profile === self::PROFILE_ADMIN ? $product['type'] : null,
                'type.in' => $profile === self::PROFILE_ADMIN ? $product['type_in'] : null,
                'sku.eq' => $profile === self::PROFILE_ADMIN ? $product['sku'] : null,
                'slug.eq' => $profile === self::PROFILE_ADMIN ? $product['slug'] : null,
                'name.like' => $profile === self::PROFILE_ADMIN ? $product['name'] : null,
                'publishable.eq' => $profile === self::PROFILE_ADMIN ? ($product['publishable'] === null ? null : ($product['publishable'] ? 1 : 0)) : null,
            ], static fn ($v) => ! ($v === null || $v === [])),
        ];

        return [
            'profile' => $profile,
            'sort' => $sort,
            'raw_filters' => $rawFilters,
            'echo' => $echo,
            'has_variant_constraints' => $hasVariantConstraints,
            'product' => $product,
            'variant' => $variant,
        ];
    }

    private function normalizeProfile(string $profile): string
    {
        return $profile === self::PROFILE_ADMIN ? self::PROFILE_ADMIN : self::PROFILE_STOREFRONT;
    }

    private function normalizeSort(string $sort, string $profile): string
    {
        $sort = trim($sort);
        $allowed = $profile === self::PROFILE_ADMIN ? self::ADMIN_SORTS : self::STOREFRONT_SORTS;

        return in_array($sort, $allowed, true)
            ? $sort
            : ($profile === self::PROFILE_ADMIN ? '-updated_at' : 'newest');
    }

    private function getScalar(array $filters, string $key): ?string
    {
        $v = $filters[$key] ?? null;
        if (is_array($v)) {
            $v = Arr::first($v);
        }
        $v = is_string($v) ? trim($v) : null;

        return ($v === '') ? null : $v;
    }

    private function getCsv(array $filters, string $key): ?array
    {
        $v = $filters[$key] ?? null;

        if (is_array($v)) {
            $v = implode(',', $v);
        }

        if (! is_string($v)) {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $v)), static fn ($x) => $x !== ''));

        return count($parts) ? $parts : null;
    }

    private function getNumeric(array $filters, string $key): ?float
    {
        $v = $filters[$key] ?? null;
        if (is_array($v)) {
            $v = Arr::first($v);
        }
        if (is_numeric($v)) {
            return (float) $v;
        }

        return null;
    }

    /**
     * Parses boolish inputs reliably:
     * 1/0, true/false, yes/no, on/off
     */
    private function getBoolish(array $filters, string $key): ?bool
    {
        $v = $filters[$key] ?? null;
        if (is_array($v)) {
            $v = Arr::first($v);
        }
        if (is_bool($v)) {
            return $v;
        }
        if (! is_string($v) && ! is_numeric($v)) {
            return null;
        }

        $s = Str::lower(trim((string) $v));
        if ($s === '') {
            return null;
        }

        if (in_array($s, ['1', 'true', 'yes', 'y', 'on'], true)) {
            return true;
        }
        if (in_array($s, ['0', 'false', 'no', 'n', 'off'], true)) {
            return false;
        }

        return null;
    }

    private function getAttrEqFilters(array $filters): array
    {
        $out = [];

        foreach ($filters as $key => $raw) {
            // attr.<code>.eq
            if (! is_string($key) || ! Str::startsWith($key, 'attr.')) {
                continue;
            }

            $parts = explode('.', $key, 3);
            if (count($parts) !== 3) {
                continue;
            }

            [, $code, $op] = $parts;
            if ($op !== 'eq') {
                continue;
            }

            $val = is_array($raw) ? Arr::first($raw) : $raw;
            $val = is_string($val) ? trim($val) : null;

            if ($code !== '' && $val !== null && $val !== '') {
                $out[$code] = $val;
            }
        }

        return $out;
    }

    private function getAttrInFilters(array $filters): array
    {
        $out = [];

        foreach ($filters as $key => $raw) {
            // attr.<code>.in
            if (! is_string($key) || ! Str::startsWith($key, 'attr.')) {
                continue;
            }

            $parts = explode('.', $key, 3);
            if (count($parts) !== 3) {
                continue;
            }

            [, $code, $op] = $parts;
            if ($op !== 'in') {
                continue;
            }

            $vals = is_array($raw) ? $raw : explode(',', (string) $raw);
            $vals = array_values(array_filter(array_map('trim', $vals), static fn ($x) => $x !== ''));

            if ($code !== '' && count($vals)) {
                $out[$code] = $vals;
            }
        }

        return $out;
    }
}
