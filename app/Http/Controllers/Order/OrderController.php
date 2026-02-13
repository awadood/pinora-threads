<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Support\ResolvesCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OrderController
 *
 * Handles checkout, customer order history, and basic admin order views.
 *
 * @author Abdul Wadood
 */
class OrderController extends Controller
{
    use ResolvesCart;

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * POST /api/cart/checkout
     *
     * Guest or auth:
     *  - resolve cart by X-Cart-Key
     *  - create order from cart using OrderService
     *  - attach cart to user and mark as checked_out
     */
    public function checkout(CheckoutRequest $request): OrderResource
    {
        $validated = $request->validated();

        $cart = $this->resolveCart($request);

        $user = Auth::user();

        $order = $this->orderService->checkoutFromCart($cart, $validated, $user);

        return OrderResource::make($order);
    }

    /**
     * GET /api/orders
     *
     * List orders for the authenticated customer.
     */
    public function indexCustomer(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('items')
            ->get();

        return OrderResource::collection($orders);
    }

    /**
     * GET /api/orders/{order}
     *
     * Show a single order belonging to the authenticated customer.
     */
    public function showCustomer(Request $request, Order $order): OrderResource
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $order->load('items');

        return OrderResource::make($order);
    }

    /**
     * GET /api/admin/orders
     *
     * Admin listing – protected via auth & (optionally) permission middleware.
     */
    public function indexAdmin(Request $request)
    {
        $orders = Order::with('items')
            ->orderByDesc('created_at')
            ->get();

        return OrderResource::collection($orders);
    }

    /**
     * GET /api/admin/orders/{order}
     */
    public function showAdmin(Order $order): OrderResource
    {
        $order->load('items');

        return OrderResource::make($order);
    }

    /**
     * PATCH /api/admin/orders/{order}/status
     *
     * Body: { "status_code": "shipped" }
     */
    public function updateStatus(Request $request, Order $order): OrderResource
    {
        $data = $request->validate([
            'status_code' => ['required', 'string', 'max:50'],
        ]);

        $this->orderService->updateOrderStatus($order, $data['status_code']);

        return OrderResource::make($order->fresh('items'));
    }
}
