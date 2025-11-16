<?php

namespace App\Services\Customer;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Repositories\Customer\Contracts\IWishlistItemRepository;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * WishlistItemService
 *
 * Manages add/remove operations for wishlist items and ensures
 * that each product/variant pair is unique per wishlist.
 *
 * @author Abdul Wadood
 */
class WishlistItemService
{
    public function __construct(
        protected IWishlistItemRepository $items
    ) {}

    /**
     * @return \Illuminate\Support\Collection<int, WishlistItem>
     */
    public function listForWishlist(Wishlist $wishlist)
    {
        return $this->items->forWishlist($wishlist->getKey());
    }

    public function addToWishlist(
        Authenticatable $user,
        Wishlist $wishlist,
        int $productId,
        ?int $productVariantId = null
    ): WishlistItem {
        if ($wishlist->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        $existing = $this->items->findUnique($wishlist->getKey(), $productId, $productVariantId);
        if ($existing) {
            return $existing;
        }

        return $this->items->create([
            'wishlist_id' => $wishlist->getKey(),
            'product_id' => $productId,
            'product_variant_id' => $productVariantId,
        ]);
    }

    public function removeFromWishlist(Authenticatable $user, WishlistItem $item): void
    {
        if ($item->wishlist->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        $this->items->destroy($item->getKey());
    }
}
