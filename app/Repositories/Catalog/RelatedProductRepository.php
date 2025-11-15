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
    /**
     * The model class handled by this repository.
     *
     * @var class-string<RelatedProduct>
     */
    protected string $modelClass = RelatedProduct::class;
}
