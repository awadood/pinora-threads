<?php

namespace App\Repositories\Payment\Contracts;

use App\Models\Payment;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * IPaymentRepository
 *
 * Repository contract for payment records (auth/capture/sale/COD).
 * Provides helpers for lookups by order, invoice, and idempotency key.
 *
 * @author Abdul Wadood
 */
interface IPaymentRepository extends IBaseRepository
{
    /**
     * Find all payments for an order.
     *
     * @return Collection<int,Payment>
     */
    public function findByOrderId(int $orderId): Collection;

    /**
     * Sum of successful payments for an invoice.
     */
    public function sumSucceededForInvoice(int $invoiceId): float;

    /**
     * Find a payment by its idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?Payment;
}
