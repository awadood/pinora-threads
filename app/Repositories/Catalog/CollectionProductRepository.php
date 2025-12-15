<?php

namespace App\Repositories\Catalog;

use App\Models\CollectionProduct;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\ICollectionProductRepository;

/**
 * CollectionProductRepository
 *
 * Concrete repository for CollectionProduct model.
 *
 * @author Abdul Wadood
 */
class CollectionProductRepository extends BaseRepository implements ICollectionProductRepository
{
    protected string $modelClass = CollectionProduct::class;
}
