<?php

namespace App\Repositories\Catalog;

use App\Models\Category;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\ICategoryRepository;

/**
 * CategoryRepository
 *
 * Concrete repository for Category model.
 *
 * @author Abdul Wadood
 */
class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    /**
     * The model class handled by this repository.
     *
     * @var class-string<Category>
     */
    protected string $modelClass = Category::class;
}
