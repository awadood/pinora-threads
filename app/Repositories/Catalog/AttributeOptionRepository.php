<?php

namespace App\Repositories\Catalog;

use App\Models\AttributeOption;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IAttributeOptionRepository;

/**
 * AttributeOptionRepository
 *
 * Concrete repository for AttributeOption model.
 *
 * @author Abdul Wadood
 */
class AttributeOptionRepository extends BaseRepository implements IAttributeOptionRepository
{
    protected string $modelClass = AttributeOption::class;
}
