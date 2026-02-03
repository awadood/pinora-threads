<?php

namespace App\Support;

final class ProductListQuery
{
    /**
     * @param  string  $profile  'storefront'|'admin'
     * @param  array  $productFilters  normalized product-level filters
     * @param  array  $detailFilters  normalized product detail filters (attributes, price, stock)
     */
    public function __construct(
        public readonly string $profile,
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $sort,

        public readonly array $productFilters,
        public readonly array $detailFilters,
        public readonly bool $hasDetailConstraints,

        public readonly string $countryCode,
        public readonly string $currencyCode,

        /** @var array<int,int> */
        public readonly array $stockIds,

        /** Echo of applied, normalized filters (for UI state). */
        public readonly array $appliedEcho,
    ) {}
}
