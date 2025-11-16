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
        'variant_id' => true,
        'user_id' => true,
        'email' => true,
    ];

    public function findExisting(int $variantId, ?int $userId, ?string $email): ?StockBackInSubscription
    {
        $query = $this->query()->where('variant_id', $variantId);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($email !== null) {
            $query->where('email', $email);
        }

        return $query->first();
    }

    public function findPendingForVariant(int $variantId): Collection
    {
        return $this->query()
            ->where('variant_id', $variantId)
            ->whereNull('notified_at')
            ->get();
    }
}
