<?php

namespace App\Repositories\Catalog;

use App\Models\ProductVariantMedia;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductVariantMediaRepository;

/**
 * ProductVariantMediaRepository
 *
 * Concrete repository for ProductVariantMedia model.
 *
 * @author Abdul Wadood
 */
class ProductVariantMediaRepository extends BaseRepository implements IProductVariantMediaRepository
{
    /**
     * The model class handled by this repository.
     *
     * @var class-string<ProductVariantMedia>
     */
    protected string $modelClass = ProductVariantMedia::class;
}
