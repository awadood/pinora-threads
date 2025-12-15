<?php

namespace App\Repositories\Catalog;

use App\Models\CategoryProduct;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\ICategoryProductRepository;

/**
 * CategoryProductRepository
 *
 * Concrete repository for CategoryProduct model.
 *
 * @author Abdul Wadood
 */
class CategoryProductRepository extends BaseRepository implements ICategoryProductRepository
{
    protected string $modelClass = CategoryProduct::class;
}
