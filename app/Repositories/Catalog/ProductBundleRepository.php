<?php

namespace App\Repositories\Catalog;

use App\Models\ProductBundle;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductBundleRepository;

/**
 * ProductBundleRepository
 *
 * Concrete repository for ProductBundle model.
 *
 * @author Abdul Wadood
 */
class ProductBundleRepository extends BaseRepository implements IProductBundleRepository
{
    /**
     * The model class handled by this repository.
     *
     * @var class-string<ProductBundle>
     */
    protected string $modelClass = ProductBundle::class;
}
