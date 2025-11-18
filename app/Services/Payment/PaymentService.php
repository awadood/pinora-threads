<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\Payment\Contracts\IPaymentRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * PaymentService
 *
 * Records successful money movement (auth/capture/sale/COD),
 * enforces currency and amount invariants, and coordinates
 * with invoices (and later with OrderService).
 *
 * @author Abdul Wadood
 */
class PaymentService
{
    public function __construct(
        protected IPaymentRepository $payments,
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Record a successful payment operation.
     *
     * @param  array{
     *     action:string,
     *     payment_method_code:string,
     *     payment_status_code:string,
     *     amount:float|int|string,
     *     idempotency_key?:string,
     *     gateway_txn_id?:string|null,
     *     request_payload?:mixed,
     *     response_payload?:mixed,
     * }  $data
     */
    public function recordPayment(Order $order, ?Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($order, $invoice, $data) {
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new InvalidArgumentException('Payment amount must be positive.');
            }

            // Currency must match order
            $currency = $order->currency_code;

            // Idempotency
            if (! empty($data['idempotency_key'])) {
                $existing = $this->payments->findByIdempotencyKey($data['idempotency_key']);
                if ($existing) {
                    return $existing;
                }
            }

            /** @var Payment $payment */
            $payment = $this->payments->create([
                'order_id' => $order->id,
                'invoice_id' => $invoice?->id,
                'currency_code' => $currency,
                'payment_method_code' => $data['payment_method_code'],
                'action' => $data['action'],
                'payment_status_code' => $data['payment_status_code'],
                'amount' => $amount,
                'gateway_txn_id' => $data['gateway_txn_id'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'processed_at' => now(),
                'request_payload' => $data['request_payload'] ?? null,
                'response_payload' => $data['response_payload'] ?? null,
            ]);

            // If this is a successful capture/sale/COD collection, mark invoice/order as paid
            if (in_array($payment->payment_status_code, ['succeeded', 'captured'], true)) {
                if ($invoice) {
                    $this->invoiceService->markPaid($invoice);
                }

                // TODO: Coordinate with OrderService from Order domain to set paid_at + status.
                $order->paid_at = $order->paid_at ?? now();
                $order->save();
            }

            return $payment;
        });
    }
}
