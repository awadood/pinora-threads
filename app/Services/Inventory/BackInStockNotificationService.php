<?php

namespace App\Services\Inventory;

use App\Models\StockBackInSubscription;
use App\Repositories\Inventory\Contracts\IStockBackInSubscriptionRepository;
use Illuminate\Support\Carbon;

/**
 * BackInStockNotificationService
 *
 * Handles creation of back-in-stock subscriptions and marks them as notified
 * when inventory becomes available. Hook this into your mail/SMS infrastructure later.
 *
 * @author Abdul Wadood
 */
class BackInStockNotificationService
{
    public function __construct(private IStockBackInSubscriptionRepository $repository) {}

    /**
     * Subscribe a user or email to back-in-stock notifications for a product.
     */
    public function subscribe(int $productId, ?int $userId, ?string $email): StockBackInSubscription
    {
        $existing = $this->repository->findExisting($productId, $userId, $email);

        if ($existing instanceof StockBackInSubscription) {
            return $existing;
        }

        /** @var StockBackInSubscription $subscription */
        $subscription = $this->repository->create([
            'product_id' => $productId,
            'user_id' => $userId,
            'email' => $email,
        ]);

        return $subscription;
    }

    /**
     * Mark all pending subscriptions for a product as notified. Integrate your
     * real notification channel here (email/SMS/WhatsApp etc.).
     *
     * @return int number of subscriptions updated
     */
    public function notifyAll(int $productId): int
    {
        $subscriptions = $this->repository->findPendingForProduct($productId);

        $now = Carbon::now();
        $updated = 0;
        foreach ($subscriptions as $subscription) {
            $subscription->update(['notified_at' => $now]);
            $updated++;
        }

        return $updated;
    }

    public function notify(StockBackInSubscription $stockBackInSubscription)
    {
        if (! $stockBackInSubscription->notified_at) {
            $stockBackInSubscription->update(['notified_at' => Carbon::now()]);
        }

        return $stockBackInSubscription;
    }
}
