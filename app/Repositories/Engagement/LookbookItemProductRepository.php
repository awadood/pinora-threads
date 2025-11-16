<?php

namespace App\Repositories\Engagement;

use App\Models\LookbookItemProduct;
use App\Repositories\BaseRepository;
use App\Repositories\Engagement\Contracts\ILookbookItemProductRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * LookbookItemProductRepository
 *
 * Eloquent-based repository for attachments between lookbook items and products.
 *
 * @author Abdul Wadood
 */
class LookbookItemProductRepository extends BaseRepository implements ILookbookItemProductRepository
{
    protected string $modelClass = LookbookItemProduct::class;

    public function getByItem(int $itemId): Collection
    {
        return $this->query()->where('lookbook_item_id', $itemId)->orderBy('sort_order')->get();
    }
}
