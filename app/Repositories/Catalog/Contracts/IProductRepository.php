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
     * Create a product.
     *
     * @param  array<string, mixed>  $data  Validated product payload.
     * @return \App\Models\Product The created product instance.
     *
     * @throws \Throwable If persistence fails and the transaction is rolled back.
     */
    public function createProduct(array $data): Product;

    /**
     * Activate (publish) a product.
     *
     * Semantics:
     * - Sets products.active = true.
     * - Enforces publish readiness rules atomically:
     *   - Complete pricing for required currencies (USD, PKR).
     *   - Required product media is present (primary thumbnail + at least one gallery image).
     *
     * Failure:
     * - Throws ValidationException (422) with field-level messages when rules are not satisfied.
     */
    public function activate(Product $product): Product;

    /**
     * Deactivate (unpublish) a product.
     *
     * Semantics:
     * - Sets products.active = false.
     * - Does not modify variant links, pricing, media, categories, collections, or inventory.
     * - Operation is atomic.
     */
    public function deactivate(Product $product): Product;
}
