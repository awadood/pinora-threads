<?php

namespace App\Repositories\Catalog\Contracts;

use App\Models\Product;
use App\Repositories\IBaseRepository;

/**
 * IProductPriceRepository — repository contract for ProductPrice model.
 *
 * Defines common CRUD and search operations via IBaseRepository.
 *
 * @author Abdul Wadood
 */
interface IProductPriceRepository extends IBaseRepository
{
    /**
     * Upsert product + variant pricing for a product (atomic).
     *
     * @param  array<string,mixed>  $payload
     * @return array{product_upserted:int, variant_upserted:int}
     */
    public function savePrices(Product $product, array $payload): array;
}
