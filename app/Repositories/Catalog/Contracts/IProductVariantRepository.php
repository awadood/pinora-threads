<?php

namespace App\Repositories\Catalog\Contracts;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * IProductVariantRepository — repository contract for ProductVariant model.
 *
 * Defines common CRUD and search operations via IBaseRepository and lookup by filters.
 *
 * @author Abdul Wadood
 */
interface IProductVariantRepository extends IBaseRepository
{
    /**
     * Lookup product variants for admin typeahead and selector UIs.
     *
     * Used by search-as-you-type fields (e.g. Stock Batches, Inventory, Bundles)
     * to quickly find sellable variants and return a small result set.
     *
     * Supports the standard `filter[field.op]` format, typically:
     * - `filter[q.like]` for free-text search
     * - optional exact filters like `filter[active.eq]`
     *
     * Results are non-paginated and intended for dropdowns/autocomplete,
     * with optional eager-loaded relations to avoid N+1 queries.
     *
     * @param  array<string,mixed>  $filters
     * @param  array<int,string>  $with
     * @return \Illuminate\Support\Collection<int,\App\Models\ProductVariant>
     */
    public function lookup(array $filters, array $with = []): Collection;

    /**
     * Create a new variant for a given product while enforcing product-variant invariants.
     *
     * Business rules enforced:
     * - Every product must have at least one default variant.
     * - If this is the first variant of the product, it is automatically marked as default.
     * - If the incoming payload marks the new variant as default, any existing default variant
     *   for the same product is unset (so only one default remains).
     *
     * Implementation notes:
     * - Recommended to run inside a DB transaction and lock sibling variants of the same product
     *   (e.g. lockForUpdate) to avoid race conditions when multiple requests create/set defaults.
     *
     * @param  \App\Models\Product  $product  Product to which the variant belongs.
     * @param  array<string, mixed>  $attributes  Validated attributes for the new variant.
     * @return \App\Models\ProductVariant The newly created variant model.
     *
     * @throws \Illuminate\Validation\ValidationException When invariants cannot be satisfied.
     */
    public function createFor(Product $product, array $data): ProductVariant;

    /**
     * Update an existing product variant while enforcing product-variant invariants.
     *
     * Business rules enforced:
     * - Every product must always have at least one default variant.
     * - If setting this variant as default, all other variants of the same product are updated
     *   to non-default (so only one default remains).
     * - Unsetting default on the current default variant is not allowed unless another variant
     *   is (or will be) default for the same product.
     * - If the update deactivates/disables a default variant, another variant must be promoted
     *   to default first to preserve the invariant.
     *
     * Implementation notes:
     * - Recommended to run inside a DB transaction and lock sibling variants (lockForUpdate)
     *   to avoid concurrency issues in default switching.
     *
     * @param  \App\Models\ProductVariant  $variant  Variant being updated.
     * @param  array<string, mixed>  $attributes  Validated attributes to update.
     * @return \App\Models\ProductVariant The updated (fresh) variant model.
     *
     * @throws \Illuminate\Validation\ValidationException When invariants cannot be satisfied.
     */
    public function update(ProductVariant $variant, array $data): ProductVariant;
}
