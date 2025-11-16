<?php

namespace App\Repositories\Customer;

use App\Models\Wishlist;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\IWishlistRepository;
use Illuminate\Support\Collection;

/**
 * WishlistRepository
 *
 * Eloquent implementation for wishlists.
 *
 * @author Abdul Wadood
 */
class WishlistRepository extends BaseRepository implements IWishlistRepository
{
    protected string $modelClass = Wishlist::class;

    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findByShareToken(string $token): ?Wishlist
    {
        return $this->query()->where('share_token', $token)->first();
    }
}
