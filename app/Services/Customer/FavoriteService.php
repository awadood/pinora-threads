<?php

namespace App\Services\Customer;

use App\Models\Favorite;
use App\Repositories\Customer\Contracts\IFavoriteRepository;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * FavoriteService
 *
 * Orchestrates add/remove/toggle operations for user favorites.
 *
 * @author Abdul Wadood
 */
class FavoriteService
{
    public function __construct(
        protected IFavoriteRepository $favorites
    ) {}

    /**
     * @return \Illuminate\Support\Collection<int, Favorite>
     */
    public function listForUser(Authenticatable $user)
    {
        return $this->favorites->forUser($user->getAuthIdentifier());
    }

    public function toggle(
        Authenticatable $user,
        int $productId,
        ?int $productVariantId = null
    ): bool {
        $existing = $this->favorites->findForUserAndProduct(
            $user->getAuthIdentifier(),
            $productId,
            $productVariantId
        );

        if ($existing) {
            $this->favorites->destroy($existing->getKey());

            return false;
        }

        $this->favorites->create([
            'user_id' => $user->getAuthIdentifier(),
            'product_id' => $productId,
            'product_variant_id' => $productVariantId,
        ]);

        return true;
    }
}
