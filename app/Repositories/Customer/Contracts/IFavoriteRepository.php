<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

/**
 * IFavoriteRepository
 *
 * Repository contract for managing customer favorites list.
 * Primarily used for toggling favorites for a user and fetching
 * their list for profile and marketing uses.
 *
 * @author Abdul Wadood
 */
interface IFavoriteRepository extends IBaseRepository
{
    /**
     * Get all favorites for a user.
     *
     * @return Collection<int, \App\Models\Favorite>
     */
    public function forUser(int $userId): Collection;

    /**
     * Find an existing favorite for a user-product(-variant) combination.
     */
    public function findForUserAndProduct(int $userId, int $productId, ?int $productVariantId = null): ?\App\Models\Favorite;
}
