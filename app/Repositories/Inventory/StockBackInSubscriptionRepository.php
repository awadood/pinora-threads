<?php

namespace App\Repositories\Inventory;

use App\Models\StockBackInSubscription;
use App\Repositories\BaseRepository;
use App\Repositories\Inventory\Contracts\IStockBackInSubscriptionRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * StockBackInSubscriptionRepository
 *
 * Eloquent implementation for back-in-stock subscriptions.
 *
 * @author Abdul Wadood
 */
class StockBackInSubscriptionRepository extends BaseRepository implements IStockBackInSubscriptionRepository
{
    protected string $modelClass = StockBackInSubscription::class;

    protected array $allowedSearchColumns = [
        'product_id' => true,
        'user_id' => true,
        'email' => true,
    ];

    public function findExisting(int $productId, ?int $userId, ?string $email): ?StockBackInSubscription
    {
        $query = $this->query()->where('product_id', $productId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($email !== null) {
            $query->where('email', $email);
        }

        return $query->first();
    }

    public function findPendingForProduct(int $productId): Collection
    {
        return $this->query()
            ->where('product_id', $productId)
            ->whereNull('notified_at')
            ->get();
    }
}
