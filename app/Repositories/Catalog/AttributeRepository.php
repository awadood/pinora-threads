<?php

namespace App\Repositories\Catalog;

use App\Models\Attribute;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IAttributeRepository;

/**
 * AttributeRepository
 *
 * Concrete repository for Attribute model.
 *
 * @author Abdul Wadood
 */
class AttributeRepository extends BaseRepository implements IAttributeRepository
{
    protected string $modelClass = Attribute::class;
}
