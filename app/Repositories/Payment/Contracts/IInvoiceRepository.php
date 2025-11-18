<?php

namespace App\Repositories\Payment\Contracts;

use App\Models\Invoice;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * IInvoiceRepository
 *
 * Repository contract for reading and managing invoices.
 * Provides helpers to locate invoices by order and to
 * compute basic aggregates.
 *
 * @author Abdul Wadood
 */
interface IInvoiceRepository extends IBaseRepository
{
    /**
     * Find all invoices for a given order.
     *
     * @return Collection<int,Invoice>
     */
    public function findByOrderId(int $orderId): Collection;

    /**
     * Find the primary invoice for an order.
     * For v1 we assume at most one.
     */
    public function findPrimaryForOrder(int $orderId): ?Invoice;
}
