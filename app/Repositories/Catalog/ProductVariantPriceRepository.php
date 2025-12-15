<?php

namespace App\Repositories\Catalog;

use App\Models\ProductVariantPrice;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductVariantPriceRepository;

/**
 * ProductVariantPriceRepository
 *
 * Concrete repository for ProductVariantPrice model.
 *
 * @author Abdul Wadood
 */
class ProductVariantPriceRepository extends BaseRepository implements IProductVariantPriceRepository
{
    protected string $modelClass = ProductVariantPrice::class;
}
