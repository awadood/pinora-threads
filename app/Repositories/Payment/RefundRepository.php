<?php

namespace App\Repositories\Payment;

use App\Models\Refund;
use App\Repositories\BaseRepository;
use App\Repositories\Payment\Contracts\IRefundRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * RefundRepository
 *
 * Concrete repository for refund records.
 *
 * @author Abdul Wadood
 */
class RefundRepository extends BaseRepository implements IRefundRepository
{
    protected string $modelClass = Refund::class;

    protected array $allowedSearchColumns = [
        'order_id' => true,
        'payment_id' => true,
        'refund_status_code' => true,
        'currency_code' => true,
    ];

    public function findByOrderId(int $orderId): Collection
    {
        /** @var Collection<int,Refund> $result */
        $result = $this->query()
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $result;
    }

    public function findByPaymentId(int $paymentId): Collection
    {
        /** @var Collection<int,Refund> $result */
        $result = $this->query()
            ->where('payment_id', $paymentId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $result;
    }

    public function findByIdempotencyKey(string $key): ?Refund
    {
        /** @var Refund|null $refund */
        $refund = $this->query()
            ->where('idempotency_key', $key)
            ->first();

        return $refund;
    }
}
