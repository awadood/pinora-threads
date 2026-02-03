<?php

namespace App\Services\Customer;

use App\Models\RecentlyViewed;
use App\Repositories\Customer\Contracts\IRecentlyViewedRepository;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * RecentlyViewedService
 *
 * Tracks product views for a user and exposes a compact
 * history for personalization.
 *
 * @author Abdul Wadood
 */
class RecentlyViewedService
{
    public function __construct(
        protected IRecentlyViewedRepository $recentlyViewed
    ) {}

    /**
     * @return \Illuminate\Support\Collection<int, RecentlyViewed>
     */
    public function listForUser(Authenticatable $user, int $limit = 20)
    {
        return $this->recentlyViewed->forUser($user->getAuthIdentifier(), $limit);
    }

    public function recordView(
        ?Authenticatable $user,
        int $productId
    ): void {
        if (! $user) {
            // future: anonymous/session-based tracking
            return;
        }

        RecentlyViewed::query()->create([
            'user_id' => $user->getAuthIdentifier(),
            'product_id' => $productId,
        ]);
    }
}
