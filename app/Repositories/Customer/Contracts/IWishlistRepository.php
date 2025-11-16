<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

/**
 * IWishlistRepository
 *
 * Repository contract for managing wishlists that belong to a user.
 * Supports multi-list accounts and public share via share_token.
 *
 * @author Abdul Wadood
 */
interface IWishlistRepository extends IBaseRepository
{
    /**
     * Get all wishlists for a user.
     *
     * @return Collection<int, \App\Models\Wishlist>
     */
    public function forUser(int $userId): Collection;

    /**
     * Find a wishlist by share token (public view).
     */
    public function findByShareToken(string $token): ?\App\Models\Wishlist;
}
