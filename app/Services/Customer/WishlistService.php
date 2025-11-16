<?php

namespace App\Services\Customer;

use App\Models\Wishlist;
use App\Repositories\Customer\Contracts\IWishlistRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

/**
 * WishlistService
 *
 * Manages lifecycle of wishlists: create, update, visibility
 * and share-token generation.
 *
 * @author Abdul Wadood
 */
class WishlistService
{
    public function __construct(
        protected IWishlistRepository $wishlists
    ) {}

    /**
     * @return \Illuminate\Support\Collection<int, Wishlist>
     */
    public function listForUser(Authenticatable $user)
    {
        return $this->wishlists->forUser($user->getAuthIdentifier());
    }

    public function createForUser(Authenticatable $user, array $data): Wishlist
    {
        $data['user_id'] = $user->getAuthIdentifier();

        if (! empty($data['public']) && empty($data['share_token'])) {
            $data['share_token'] = (string) Str::uuid();
        }

        return $this->wishlists->create($data);
    }

    public function updateForUser(Authenticatable $user, Wishlist $wishlist, array $data): Wishlist
    {
        if ($wishlist->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        if (array_key_exists('public', $data)) {
            if ($data['public'] && empty($wishlist->share_token)) {
                $data['share_token'] = (string) Str::uuid();
            }

            if (! $data['public']) {
                $data['share_token'] = null;
            }
        }

        $wishlist->fill($data)->save();

        return $wishlist;
    }

    public function deleteForUser(Authenticatable $user, Wishlist $wishlist): void
    {
        if ($wishlist->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        $this->wishlists->destroy($wishlist->getKey());
    }

    public function findShared(string $token): ?Wishlist
    {
        return $this->wishlists->findByShareToken($token);
    }
}
