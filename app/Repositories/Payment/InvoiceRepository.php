<?php

namespace App\Repositories\Payment;

use App\Models\Invoice;
use App\Repositories\BaseRepository;
use App\Repositories\Payment\Contracts\IInvoiceRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * InvoiceRepository
 *
 * Concrete repository for invoices.
 *
 * @author Abdul Wadood
 */
class InvoiceRepository extends BaseRepository implements IInvoiceRepository
{
    protected string $modelClass = Invoice::class;

    protected array $allowedSearchColumns = [
        'order_id' => true,
        'invoice_status_code' => true,
        'currency_code' => true,
    ];

    public function findByOrderId(int $orderId): Collection
    {
        /** @var Collection<int,Invoice> $result */
        $result = $this->query()
            ->where('order_id', $orderId)
            ->orderBy('issued_at', 'desc')
            ->get();

        return $result;
    }

    public function findPrimaryForOrder(int $orderId): ?Invoice
    {
        /** @var Invoice|null $invoice */
        $invoice = $this->query()
            ->where('order_id', $orderId)
            ->orderBy('issued_at', 'asc')
            ->first();

        return $invoice;
    }
}
