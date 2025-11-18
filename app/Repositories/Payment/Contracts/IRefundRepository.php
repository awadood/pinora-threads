<?php

namespace App\Repositories\Payment\Contracts;

use App\Models\Refund;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * IRefundRepository
 *
 * Repository contract for refund records.
 * Used to track requested, processing, and completed refunds
 * per order and payment.
 *
 * @author Abdul Wadood
 */
interface IRefundRepository extends IBaseRepository
{
    /**
     * Find all refunds for an order.
     *
     * @return Collection<int,Refund>
     */
    public function findByOrderId(int $orderId): Collection;

    /**
     * Find all refunds for a specific payment.
     *
     * @return Collection<int,Refund>
     */
    public function findByPaymentId(int $paymentId): Collection;

    /**
     * Find a refund by idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?Refund;
}
