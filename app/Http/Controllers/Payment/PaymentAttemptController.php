<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\PaymentAttemptResource;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Services\Payment\PaymentAttemptService;
use Illuminate\Http\Request;

/**
 * PaymentAttemptController
 *
 * Customer + admin endpoints for payment attempts.
 * For now, gateway integration is stubbed.
 *
 * @author Abdul Wadood
 */
class PaymentAttemptController extends Controller
{
    public function __construct(
        protected PaymentAttemptService $attemptService
    ) {}

    // --------- Customer: list attempts for own order ---------

    public function indexForOrder(Request $request, Order $order)
    {
        $user = $request->user();
        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $attempts = PaymentAttempt::query()
            ->where('order_id', $order->id)
            ->orderByDesc('attempted_at')
            ->get();

        return PaymentAttemptResource::collection($attempts);
    }

    public function storeForOrder(Request $request, Order $order)
    {
        $user = $request->user();
        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $data = $request->validate([
            'method' => ['required', 'string', 'max:50'], // 'stripe', 'paypal', etc.
            'action' => ['required', 'string', 'max:50'], // 'sale', 'auth', etc.
            'amount' => ['required', 'numeric', 'min:0.01'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'request_payload' => ['nullable', 'array'],
        ]);

        $attempt = $this->attemptService->startAttempt($order, $data);

        // TODO: plug in real gateway call; for now we just return the attempt as 'pending'
        return PaymentAttemptResource::make($attempt);
    }

    // --------- Admin list / show attempts ---------

    public function index(Request $request)
    {
        $query = PaymentAttempt::query()->with('order');

        if ($request->filled('order_id')) {
            $query->where('order_id', (int) $request->query('order_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $items = $query->orderByDesc('attempted_at')->get();

        return PaymentAttemptResource::collection($items);
    }

    public function show(PaymentAttempt $attempt)
    {
        $attempt->load('order');

        return PaymentAttemptResource::make($attempt);
    }
}
