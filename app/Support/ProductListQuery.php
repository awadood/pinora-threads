<?php

namespace App\Support;

final class ProductListQuery
{
    /**
     * @param  string  $profile  'storefront'|'admin'
     * @param  array  $productFilters  normalized product-level filters
     * @param  array  $variantFilters  normalized variant-level filters (match-any-variant)
     */
    public function __construct(
        public readonly string $profile,
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $sort,

        public readonly array $productFilters,
        public readonly array $variantFilters,
        public readonly bool $hasVariantConstraints,

        public readonly string $countryCode,
        public readonly string $currencyCode,

        /** @var array<int,int> */
        public readonly array $stockIds,

        /** Echo of applied, normalized filters (for UI state). */
        public readonly array $appliedEcho,
    ) {}
}
