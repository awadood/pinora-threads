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
    protected string $modelClass = ProductBundle::class;
}
