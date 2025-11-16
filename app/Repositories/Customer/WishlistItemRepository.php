<?php

namespace App\Repositories\Customer;

use App\Models\WishlistItem;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\IWishlistItemRepository;
use Illuminate\Support\Collection;

/**
 * WishlistItemRepository
 *
 * Eloquent implementation for wishlist items.
 *
 * @author Abdul Wadood
 */
class WishlistItemRepository extends BaseRepository implements IWishlistItemRepository
{
    protected string $modelClass = WishlistItem::class;

    public function forWishlist(int $wishlistId): Collection
    {
        return $this->query()
            ->where('wishlist_id', $wishlistId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findUnique(
        int $wishlistId,
        int $productId,
        ?int $productVariantId = null
    ): ?WishlistItem {
        $query = $this->query()
            ->where('wishlist_id', $wishlistId)
            ->where('product_id', $productId);

        if ($productVariantId !== null) {
            $query->where('product_variant_id', $productVariantId);
        } else {
            $query->whereNull('product_variant_id');
        }

        return $query->first();
    }
}
