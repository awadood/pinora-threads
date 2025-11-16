<?php

namespace App\Repositories\Engagement;

use App\Models\LookbookItem;
use App\Repositories\BaseRepository;
use App\Repositories\Engagement\Contracts\ILookbookItemRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * LookbookItemRepository
 *
 * Eloquent-based repository for lookbook items (styled looks).
 *
 * @author Abdul Wadood
 */
class LookbookItemRepository extends BaseRepository implements ILookbookItemRepository
{
    protected string $modelClass = LookbookItem::class;

    public function getByLookbook(int $lookbookId): Collection
    {
        return $this->query()->where('lookbook_id', $lookbookId)->orderBy('sort_order')->get();
    }
}
