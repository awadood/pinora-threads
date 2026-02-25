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

    public function forWishlist(int $wishlistId, string $currencyCode): Collection
    {
        return $this->query()
            ->where('wishlist_id', $wishlistId)
            ->with([
                'product' => function ($productQuery) use ($currencyCode) {
                    $productQuery
                        ->where('active', true)
                        ->withCount('variants as variants_count')
                        ->with([
                            'prices' => fn ($priceQuery) => $priceQuery->where('currency_code', $currencyCode),
                            'thumbnailMedia.asset.renditions',
                        ]);
                },
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function findUnique(
        int $wishlistId,
        int $productId
    ): ?WishlistItem {
        return $this->query()
            ->where('wishlist_id', $wishlistId)
            ->where('product_id', $productId)
            ->first();
    }
}
