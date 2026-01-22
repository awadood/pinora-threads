<?php

namespace App\Repositories\Catalog\Contracts;

use App\Models\Product;
use App\Repositories\IBaseRepository;
use App\Support\ProductListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * Lookup products for admin typeahead and selector UIs.
     *
     * Used by search-as-you-type fields or filter components
     * (e.g. Stock Batches, Inventory, Bundles, Filter)
     * to quickly find sellable variants and return a small result set.
     *
     * Supports the standard `filter[field.op]` format, typically:
     *
     * product-level:
     * - filter[active.eq]=1
     * - filter[category.slug.eq]=unstitched
     * - filter[collection.slug.eq]=eid-edit
     *
     * variant attribute (match any variant):
     * - filter[attr.fabric_type.in]=lawn,cotton
     * - filter[attr.color_family.in]=red,blue
     *
     * price range (currency from store_ctx):
     * - filter[price.gte]=10000
     * - filter[price.lte]=15000
     *
     * optional stock-only:
     * - filter[in_stock.eq]=1 (means: there exists a matching variant with qty > 0 in the ctx stock)
     *
     * Results are paginated and intended for dropdowns/autocomplete,
     * with optional eager-loaded relations to avoid N+1 queries.
     *
     * @param  \App\Support\ProductListQuery  $query
     * @param  array<int,string>  $with
     */
    public function lookup(ProductListQuery $q): LengthAwarePaginator;

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

    /**
     * Activate (publish) a product.
     *
     * Semantics:
     * - Sets products.active = true.
     * - Enforces publish readiness rules atomically:
     *   - A default variant exists and is active.
     *   - All active variants have complete pricing for required currencies (USD, PKR).
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
     * - Does not modify variants, pricing, media, categories, collections, or inventory.
     * - Operation is atomic.
     */
    public function deactivate(Product $product): Product;
}
