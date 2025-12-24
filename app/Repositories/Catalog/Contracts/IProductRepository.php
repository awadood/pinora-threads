<?php

namespace App\Repositories\Catalog\Contracts;

use App\Models\Product;
use App\Repositories\IBaseRepository;

/**
 * IProductRepository — repository contract for Product model.
 *
 * Defines common CRUD and search operations via IBaseRepository.
 *
 * @author Abdul Wadood
 */
interface IProductRepository extends IBaseRepository
{
    /**
     * Create a product and ensure it has a valid default variant at creation time.
     *
     * Business rules enforced:
     * - Every product must have at least one variant.
     * - Exactly one variant is marked as the default variant for the product.
     * - the first variant is normalized to default=true and all other variants are normalized to default=false.
     * - a default variant is auto-created using the minimal required fields derived from the
     *   product data (per domain rules).
     *
     * Implementation notes:
     * - Must run within a single DB transaction to keep product + variants consistent.
     * - Recommended to lock/guard against concurrent creations that could result in multiple
     *   defaults (typically handled by transaction boundaries here).
     *
     * @param  array<string, mixed>  $data  Validated product payload, optionally including variant data.
     * @return \App\Models\Product The created product instance (optionally eager-loaded with variants).
     *
     * @throws \Throwable If persistence fails and the transaction is rolled back.
     */
    public function createWithDefaultVariant(array $data): Product;
}
