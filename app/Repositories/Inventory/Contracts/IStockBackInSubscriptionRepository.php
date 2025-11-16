<?php

namespace App\Repositories\Inventory\Contracts;

use App\Models\StockBackInSubscription;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * IStockBackInSubscriptionRepository
 *
 * Repository contract for managing back-in-stock subscriptions.
 *
 * @author Abdul Wadood
 */
interface IStockBackInSubscriptionRepository extends IBaseRepository
{
    /**
     * Find existing subscription for the same variant + (user or email).
     */
    public function findExisting(int $variantId, ?int $userId, ?string $email): ?StockBackInSubscription;

    /**
     * Find pending (not yet notified) subscriptions for a given variant.
     *
     * @return Collection<int, StockBackInSubscription>
     */
    public function findPendingForVariant(int $variantId): Collection;
}
