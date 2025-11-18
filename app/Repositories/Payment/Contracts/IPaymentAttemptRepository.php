<?php

namespace App\Repositories\Payment\Contracts;

use App\Models\PaymentAttempt;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * IPaymentAttemptRepository
 *
 * Repository contract for payment attempts.
 * Each attempt represents a single try at paying,
 * including failures and 3DS flows.
 *
 * @author Abdul Wadood
 */
interface IPaymentAttemptRepository extends IBaseRepository
{
    /**
     * Find recent attempts for a given order.
     *
     * @return Collection<int,PaymentAttempt>
     */
    public function findRecentByOrderId(int $orderId, int $limit = 10): Collection;

    /**
     * Find an attempt by idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?PaymentAttempt;
}
