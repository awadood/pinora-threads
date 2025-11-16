<?php

namespace App\Repositories\Engagement\Contracts;

use App\Models\LookbookItemProduct;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * ILookbookItemProductRepository
 *
 * Repository contract for managing product attachments to lookbook items.
 *
 * @author Abdul Wadood
 */
interface ILookbookItemProductRepository extends IBaseRepository
{
    /**
     * Get all product attachments for a given lookbook item.
     *
     * @return Collection<int, LookbookItemProduct>
     */
    public function getByItem(int $itemId): Collection;
}
