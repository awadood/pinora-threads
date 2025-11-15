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
    /**
     * The model class handled by this repository.
     *
     * @var class-string<ProductVariantPrice>
     */
    protected string $modelClass = ProductVariantPrice::class;
}
