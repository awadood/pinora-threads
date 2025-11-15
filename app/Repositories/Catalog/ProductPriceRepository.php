<?php

namespace App\Repositories\Catalog;

use App\Models\ProductPrice;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductPriceRepository;

/**
 * ProductPriceRepository
 *
 * Concrete repository for ProductPrice model.
 *
 * @author Abdul Wadood
 */
class ProductPriceRepository extends BaseRepository implements IProductPriceRepository
{
    /**
     * The model class handled by this repository.
     *
     * @var class-string<ProductPrice>
     */
    protected string $modelClass = ProductPrice::class;
}
