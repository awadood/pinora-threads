<?php

namespace App\Repositories\Engagement\Contracts;

use App\Models\LookbookItem;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * ILookbookItemRepository
 *
 * Repository contract for managing items within a lookbook.
 *
 * @author Abdul Wadood
 */
interface ILookbookItemRepository extends IBaseRepository
{
    /**
     * Get all items that belong to a given lookbook.
     *
     * @return Collection<int, LookbookItem>
     */
    public function getByLookbook(int $lookbookId): Collection;
}
