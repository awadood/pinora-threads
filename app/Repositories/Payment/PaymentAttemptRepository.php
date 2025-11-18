<?php

namespace App\Repositories\Payment;

use App\Models\PaymentAttempt;
use App\Repositories\BaseRepository;
use App\Repositories\Payment\Contracts\IPaymentAttemptRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * PaymentAttemptRepository
 *
 * Concrete repository for payment attempts.
 *
 * @author Abdul Wadood
 */
class PaymentAttemptRepository extends BaseRepository implements IPaymentAttemptRepository
{
    protected string $modelClass = PaymentAttempt::class;

    protected array $allowedSearchColumns = [
        'order_id' => true,
        'status' => true,
        'method' => true,
        'currency_code' => true,
    ];

    public function findRecentByOrderId(int $orderId, int $limit = 10): Collection
    {
        /** @var Collection<int,PaymentAttempt> $result */
        $result = $this->query()
            ->where('order_id', $orderId)
            ->orderBy('attempted_at', 'desc')
            ->limit($limit)
            ->get();

        return $result;
    }

    public function findByIdempotencyKey(string $key): ?PaymentAttempt
    {
        /** @var PaymentAttempt|null $attempt */
        $attempt = $this->query()
            ->where('idempotency_key', $key)
            ->first();

        return $attempt;
    }
}
