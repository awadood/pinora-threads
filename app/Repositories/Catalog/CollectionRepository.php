<?php

namespace App\Repositories\Catalog;

use App\Models\Collection;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\ICollectionRepository;

/**
 * CollectionRepository
 *
 * Concrete repository for Collection model.
 *
 * @author Abdul Wadood
 */
class CollectionRepository extends BaseRepository implements ICollectionRepository
{
    protected string $modelClass = Collection::class;
}
