<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Order;
use App\Repositories\Payment\Contracts\IInvoiceRepository;
use Illuminate\Support\Facades\DB;

/**
 * InvoiceService
 *
 * Encapsulates creation and lifecycle of invoices for orders.
 *
 * @author Abdul Wadood
 */
class InvoiceService
{
    public function __construct(
        protected IInvoiceRepository $invoices
    ) {}

    public function createForOrder(Order $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $existing = $this->invoices->findPrimaryForOrder($order->id);
            if ($existing) {
                return $existing;
            }

            // Basic invoice number strategy: timestamp + order id
            $number = (int) (now()->timestamp.$order->id);

            /** @var Invoice $invoice */
            $invoice = $this->invoices->create([
                'order_id' => $order->id,
                'number' => $number,
                'currency_code' => $order->currency_code,
                'amount_due' => $order->total,
                'invoice_status_code' => 'issued', // ensure this exists in invoice_statuses
                'issued_at' => now(),
                'due_at' => null,
                'paid_at' => null,
                'meta' => null,
            ]);

            return $invoice;
        });
    }

    public function getPrimaryForOrder(Order $order): ?Invoice
    {
        return $this->invoices->findPrimaryForOrder($order->id);
    }

    public function markPaid(Invoice $invoice): void
    {
        $invoice->invoice_status_code = 'paid';
        $invoice->paid_at = $invoice->paid_at ?? now();
        $invoice->save();
    }

    public function void(Invoice $invoice, ?string $reason = null): void
    {
        $meta = $invoice->meta ?? [];
        if (is_array($meta)) {
            $meta['void_reason'] = $reason;
        } else {
            $meta = ['void_reason' => $reason];
        }

        $invoice->invoice_status_code = 'voided';
        $invoice->meta = $meta;
        $invoice->save();
    }
}
