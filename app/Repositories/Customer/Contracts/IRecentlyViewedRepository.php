<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

/**
 * IRecentlyViewedRepository
 *
 * Repository contract for tracking recently viewed products
 * for signed-in or anonymous users.
 *
 * @author Abdul Wadood
 */
interface IRecentlyViewedRepository extends IBaseRepository
{
    /**
     * Return a time-ordered list of recently viewed items for a user.
     *
     * @return Collection<int, \App\Models\RecentlyViewed>
     */
    public function forUser(int $userId, int $limit = 20): Collection;
}
