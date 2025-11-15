<?php

namespace App\Repositories\Catalog;

use App\Models\ProductVariant;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductVariantRepository;

/**
 * ProductVariantRepository
 *
 * Concrete repository for ProductVariant model.
 *
 * @author Abdul Wadood
 */
class ProductVariantRepository extends BaseRepository implements IProductVariantRepository
{
    /**
     * The model class handled by this repository.
     *
     * @var class-string<ProductVariant>
     */
    protected string $modelClass = ProductVariant::class;
}
