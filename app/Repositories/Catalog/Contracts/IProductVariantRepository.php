<?php

namespace App\Repositories\Catalog\Contracts;

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
}
