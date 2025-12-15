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
    protected string $modelClass = ProductPrice::class;
}
