<?php

namespace App\Repositories\Catalog;

use App\Models\ProductMedia;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductMediaRepository;

/**
 * ProductMediaRepository
 *
 * Concrete repository for ProductMedia model.
 *
 * @author Abdul Wadood
 */
class ProductMediaRepository extends BaseRepository implements IProductMediaRepository
{
    /**
     * The model class handled by this repository.
     *
     * @var class-string<ProductMedia>
     */
    protected string $modelClass = ProductMedia::class;
}
