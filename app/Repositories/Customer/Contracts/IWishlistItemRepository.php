<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

/**
 * IWishlistItemRepository
 *
 * Repository contract for managing items inside a wishlist.
 * Helps ensure uniqueness and convenient lookups.
 *
 * @author Abdul Wadood
 */
interface IWishlistItemRepository extends IBaseRepository
{
    /**
     * Get all items for a wishlist.
     *
     * @return Collection<int, \App\Models\WishlistItem>
     */
    public function forWishlist(int $wishlistId): Collection;

    /**
     * Find an item by wishlist + product.
     */
    public function findUnique(int $wishlistId, int $productId): ?\App\Models\WishlistItem;
}
