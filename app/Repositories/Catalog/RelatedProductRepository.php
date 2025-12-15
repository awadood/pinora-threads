<?php

namespace App\Repositories\Catalog;

use App\Models\RelatedProduct;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IRelatedProductRepository;

/**
 * RelatedProductRepository
 *
 * Concrete repository for RelatedProduct model.
 *
 * @author Abdul Wadood
 */
class RelatedProductRepository extends BaseRepository implements IRelatedProductRepository
{
    protected string $modelClass = RelatedProduct::class;
}
