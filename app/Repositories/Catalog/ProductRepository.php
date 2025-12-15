<?php

namespace App\Repositories\Catalog;

use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductRepository;

/**
 * ProductRepository
 *
 * Concrete repository for Product model.
 *
 * @author Abdul Wadood
 */
class ProductRepository extends BaseRepository implements IProductRepository
{
    protected string $modelClass = Product::class;
}
