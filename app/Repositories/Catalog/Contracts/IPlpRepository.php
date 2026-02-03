<?php

namespace App\Repositories\Catalog\Contracts;

use App\Support\ProductListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * IPlpRepository — repository contract for product listing.
 *
 * @author Abdul Wadood
 */
interface IPlpRepository
{
    /**
     * List products for admin typeahead and selector UIs.
     *
     * Used by search-as-you-type fields or filter components
     * (e.g. Stock Batches, Inventory, Bundles, Filter)
     * to quickly find sellable products and return a small result set.
     *
     * Supports the standard `filter[field.op]` format, typically:
     *
     * product-level:
     * - filter[active.eq]=1
     * - filter[category.slug.eq]=unstitched
     * - filter[collection.slug.eq]=eid-edit
     *
     * product attribute:
     * - filter[attr.fabric_type.in]=lawn,cotton
     * - filter[attr.color_family.in]=red,blue
     *
     * price range (currency from store_ctx):
     * - filter[price.gte]=10000
     * - filter[price.lte]=15000
     *
     * optional stock-only:
     * - filter[in_stock.eq]=1 (means: there exists a product with qty > 0 in the ctx stock)
     *
     * Results are paginated and intended for dropdowns/autocomplete,
     * with optional eager-loaded relations to avoid N+1 queries.
     *
     * @param  \App\Support\ProductListQuery  $query
     * @param  array<int,string>  $with
     */
    public function list(ProductListQuery $q): LengthAwarePaginator;
}
