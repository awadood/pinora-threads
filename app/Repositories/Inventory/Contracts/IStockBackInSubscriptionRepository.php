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
     * Find existing subscription for the same product + (user or email).
     */
    public function findExisting(int $productId, ?int $userId, ?string $email): ?StockBackInSubscription;

    /**
     * Find pending (not yet notified) subscriptions for a given product.
     *
     * @return Collection<int, StockBackInSubscription>
     */
    public function findPendingForProduct(int $productId): Collection;
}
