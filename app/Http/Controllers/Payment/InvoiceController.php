<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\InvoiceResource;
use App\Models\Invoice;
use App\Models\Order;
use App\Repositories\Payment\Contracts\IInvoiceRepository;
use App\Services\Payment\InvoiceService;
use Illuminate\Http\Request;

/**
 * InvoiceController
 *
 * Customer + admin endpoints for invoices.
 *
 * @author Abdul Wadood
 */
class InvoiceController extends Controller
{
    public function __construct(
        protected IInvoiceRepository $invoices,
        protected InvoiceService $invoiceService) {}

    public function indexCustomer(Request $request)
    {
        $user = $request->user();

        $items = $this->invoices->query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('issued_at')
            ->get();

        return InvoiceResource::collection($items);
    }

    public function showCustomer(Request $request, Invoice $invoice)
    {
        $user = $request->user();

        /** @var Order $order */
        $order = $invoice->order; // assume relation
        if ($order->user_id !== $user->id) {
            abort(404);
        }

        return InvoiceResource::make($invoice);
    }

    // ---------------- Admin ----------------

    public function index(Request $request)
    {
        $query = $this->invoices->query()->with('order');

        if ($request->filled('status')) {
            $query->where('invoice_status_code', $request->query('status'));
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', (int) $request->query('order_id'));
        }

        $items = $query->orderByDesc('issued_at')->get();

        return InvoiceResource::collection($items);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('order');

        return InvoiceResource::make($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'due_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
            'invoice_status_code' => ['nullable', 'string', 'max:50'],
        ]);

        if (array_key_exists('due_at', $data)) {
            $invoice->due_at = $data['due_at'];
        }

        if (array_key_exists('meta', $data)) {
            $invoice->meta = $data['meta'];
        }

        if (! empty($data['invoice_status_code'])) {
            $invoice->invoice_status_code = $data['invoice_status_code'];
        }

        $invoice->save();

        return InvoiceResource::make($invoice);
    }
}
