<?php

namespace App\Repositories\Payment;

use App\Models\Payment;
use App\Repositories\BaseRepository;
use App\Repositories\Payment\Contracts\IPaymentRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * PaymentRepository
 *
 * Concrete repository for payments (auth/capture/sale/COD).
 *
 * @author Abdul Wadood
 */
class PaymentRepository extends BaseRepository implements IPaymentRepository
{
    protected string $modelClass = Payment::class;

    protected array $allowedSearchColumns = [
        'order_id' => true,
        'invoice_id' => true,
        'payment_status_code' => true,
        'payment_method_code' => true,
        'currency_code' => true,
    ];

    public function findByOrderId(int $orderId): Collection
    {
        /** @var Collection<int,Payment> $result */
        $result = $this->query()
            ->where('order_id', $orderId)
            ->orderBy('processed_at', 'desc')
            ->get();

        return $result;
    }

    public function sumSucceededForInvoice(int $invoiceId): float
    {
        return (float) $this->query()
            ->where('invoice_id', $invoiceId)
            ->where('payment_status_code', 'succeeded')
            ->sum('amount');
    }

    public function findByIdempotencyKey(string $key): ?Payment
    {
        /** @var Payment|null $payment */
        $payment = $this->query()
            ->where('idempotency_key', $key)
            ->first();

        return $payment;
    }
}
