<?php

namespace App\Repositories\Customer;

use App\Models\Favorite;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\IFavoriteRepository;
use Illuminate\Support\Collection;

/**
 * FavoriteRepository
 *
 * Eloquent implementation for customer favorites list.
 *
 * @author Abdul Wadood
 */
class FavoriteRepository extends BaseRepository implements IFavoriteRepository
{
    protected string $modelClass = Favorite::class;

    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findForUserAndProduct(
        int $userId,
        int $productId,
        ?int $productVariantId = null
    ): ?Favorite {
        $query = $this->query()
            ->where('user_id', $userId)
            ->where('product_id', $productId);

        if ($productVariantId !== null) {
            $query->where('product_variant_id', $productVariantId);
        } else {
            $query->whereNull('product_variant_id');
        }

        return $query->first();
    }
}
