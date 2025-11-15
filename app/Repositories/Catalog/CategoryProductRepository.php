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
    /**
     * The model class handled by this repository.
     *
     * @var class-string<CategoryProduct>
     */
    protected string $modelClass = CategoryProduct::class;
}
