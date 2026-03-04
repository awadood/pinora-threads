<?php

namespace App\Support\Storefront;

final class StoreContext
{
    public function __construct(
        public readonly string $country,
        public readonly string $currency,
        public readonly string $source, // query|cookie|cloudfront|default
    ) {}
}
