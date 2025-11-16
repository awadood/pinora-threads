<?php

namespace App\Repositories\Customer;

use App\Models\RecentlyViewed;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\IRecentlyViewedRepository;
use Illuminate\Support\Collection;

/**
 * RecentlyViewedRepository
 *
 * Eloquent implementation for recently viewed products.
 *
 * @author Abdul Wadood
 */
class RecentlyViewedRepository extends BaseRepository implements IRecentlyViewedRepository
{
    protected string $modelClass = RecentlyViewed::class;

    public function forUser(int $userId, int $limit = 20): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->get();
    }
}
