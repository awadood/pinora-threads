<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\InvoiceService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

/**
 * PaymentController
 *
 * Admin endpoints for listing payments and recording COD collections.
 *
 * @author Abdul Wadood
 */
class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected InvoiceService $invoiceService
    ) {}

    public function index(Request $request)
    {
        $query = Payment::query()->with(['order', 'invoice']);

        if ($request->filled('order_id')) {
            $query->where('order_id', (int) $request->query('order_id'));
        }

        if ($request->filled('status')) {
            $query->where('payment_status_code', $request->query('status'));
        }

        $items = $query
            ->orderByDesc('processed_at')
            ->get();

        return PaymentResource::collection($items);
    }

    public function show(Payment $payment)
    {
        $payment->load(['order', 'invoice']);

        return PaymentResource::make($payment);
    }

    /**
     * POST /api/admin/payments/cod-collection
     *
     * Record a COD collection event for a PK order.
     */
    public function codCollection(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var Order $order */
        $order = Order::findOrFail($data['order_id']);

        $invoice = null;
        if (! empty($data['invoice_id'])) {
            $invoice = $this->invoiceService->getPrimaryForOrder($order);
            if (! $invoice || $invoice->id !== (int) $data['invoice_id']) {
                // fallback: just load invoice by id
                $invoice = $order->invoices()
                    ->where('id', (int) $data['invoice_id'])
                    ->first();
            }
        } else {
            $invoice = $this->invoiceService->getPrimaryForOrder($order)
                ?? $this->invoiceService->createForOrder($order);
        }

        $payment = $this->paymentService->recordPayment($order, $invoice, [
            'action' => 'cod_collection',
            'payment_method_code' => 'cod',
            'payment_status_code' => 'succeeded',
            'amount' => $data['amount'],
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'gateway_txn_id' => null,
            'request_payload' => null,
            'response_payload' => null,
        ]);

        return PaymentResource::make($payment);
    }
}
