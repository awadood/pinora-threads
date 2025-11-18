<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\RefundResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Repositories\Payment\Contracts\IRefundRepository;
use App\Services\Payment\RefundService;
use Illuminate\Http\Request;

/**
 * RefundController
 *
 * Admin endpoints for listing and creating refunds.
 *
 * @author Abdul Wadood
 */
class RefundController extends Controller
{
    public function __construct(
        protected RefundService $refundService,
        protected IRefundRepository $refunds
    ) {}

    public function index(Request $request)
    {
        $query = Refund::query()->with(['order', 'payment']);

        if ($request->filled('order_id')) {
            $query->where('order_id', (int) $request->query('order_id'));
        }

        if ($request->filled('status')) {
            $query->where('refund_status_code', $request->query('status'));
        }

        $items = $query->orderByDesc('created_at')->get();

        return RefundResource::collection($items);
    }

    public function show(Refund $refund)
    {
        $refund->load(['order', 'payment']);

        return RefundResource::make($refund);
    }

    /**
     * POST /api/admin/orders/{order}/refunds
     */
    public function store(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var Payment $payment */
        $payment = Payment::where('order_id', $order->id)
            ->where('id', $data['payment_id'])
            ->firstOrFail();

        $refund = $this->refundService->requestRefund($order, $payment, $data);

        return RefundResource::make($refund)->response()->setStatusCode(201);
    }

    /**
     * PATCH /api/admin/refunds/{refund}
     *
     * For now, allow updating refund_status_code manually if needed.
     */
    public function update(Request $request, Refund $refund)
    {
        $data = $request->validate([
            'refund_status_code' => ['required', 'string', 'max:50'],
            'gateway_refund_id' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
        ]);

        $refund->refund_status_code = $data['refund_status_code'];
        if (array_key_exists('gateway_refund_id', $data)) {
            $refund->gateway_refund_id = $data['gateway_refund_id'];
        }
        if (array_key_exists('reason', $data)) {
            $refund->reason = $data['reason'];
        }

        if (in_array($refund->refund_status_code, ['succeeded', 'failed'], true)) {
            $refund->processed_at = $refund->processed_at ?? now();
        }

        $refund->save();

        return RefundResource::make($refund);
    }
}
