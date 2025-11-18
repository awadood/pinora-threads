<?php

namespace App\Services\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductPrice;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Repositories\Order\Contracts\IOrderRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * OrderService
 *
 * Orchestrates creation of orders from carts, including:
 *  - guest or authenticated checkout
 *  - user resolution/creation
 *  - pricing snapshot from catalog tables
 *
 * @author Abdul Wadood
 */
class OrderService
{
    public function __construct(protected IOrderRepository $orders) {}

    /**
     * Create an order from the given cart.
     *
     * Expected payload keys (validated in CheckoutRequest):
     *  - email, first_name, last_name
     *  - billing_address, shipping_address (arrays)
     *  - billing_address_id, shipping_address_id (optional)
     */
    public function checkoutFromCart(Cart $cart, array $payload, ?User $authUser = null): Order
    {
        if ($cart->items()->count() === 0) {
            throw new RuntimeException('Cart is empty.');
        }

        $user = $this->resolveOrCreateUser($payload, $authUser);

        // Attach cart to user if not already
        if (! $cart->user_id) {
            $cart->user_id = $user->id;
            $cart->save();
        }

        $currency = $cart->currency_code;

        return DB::transaction(function () use ($cart, $user, $payload, $currency) {
            // 1. Compute totals
            $itemsSubtotal = 0.00;
            $totalDiscount = 0.00;
            $totalTax = 0.00;
            $totalShipping = 0.00;

            $cart->load('items.productVariant.product');

            $orderItemsData = [];

            foreach ($cart->items as $item) {
                $variant = $item->productVariant;
                $product = $variant->product;

                $unitPrice = $this->resolveUnitPrice($variant->id, $product->id, $currency);

                $lineSubtotal = $unitPrice * $item->quantity;
                $lineDiscount = 0.00;
                $lineTax = 0.00;
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $itemsSubtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;
                $totalTax += $lineTax;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'sku' => $variant->sku,
                    'variant' => [
                        'id' => $variant->id,
                        'title' => $variant->title,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                    'discount' => $lineDiscount,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                ];
            }

            $total = $itemsSubtotal - $totalDiscount + $totalTax + $totalShipping;

            // 2. Generate order number (unix timestamp with retry on collision)
            $orderNumber = $this->generateOrderNumber();

            // 3. Create order
            $orderAttributes = [
                'number' => $orderNumber,
                'user_id' => $user->id,
                'currency_code' => $currency,
                'billing_address_id' => $payload['billing_address_id'] ?? null,
                'shipping_address_id' => $payload['shipping_address_id'] ?? null,
                'billing_address' => $payload['billing_address'],
                'shipping_address' => $payload['shipping_address'],
                'tax_inclusive' => false, // can be adjusted via future TaxService
                'items_subtotal' => $itemsSubtotal,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
                'total_shipping' => $totalShipping,
                'total' => $total,
                'discount' => null,
                'shipment' => null,
                'promotions' => null,
                'taxes' => null,
                'payment_method' => null,
                'payment_txn_id' => null,
                'idempotency_key' => null,
                'shipping_method' => null,
                'tracking_number' => null,
                'carrier' => null,
            ];

            $order = $this->orders->createOrder($orderAttributes);

            // 4. Create order items
            foreach ($orderItemsData as $itemData) {
                $this->orders->createOrderItem($order, $itemData);
            }

            // 5. Mark cart as checked out
            $cart->checked_out_at = now();
            $cart->save();

            return $order->fresh(['items']);
        });
    }

    /**
     * Update order status code. Later you can push side effects:
     *  - stock movements
     *  - shipments
     *  - notifications
     */
    public function updateOrderStatus(Order $order, string $statusCode): void
    {
        $order->order_status_code = $statusCode;
        $order->save();
    }

    protected function resolveOrCreateUser(array $payload, ?User $authUser = null): User
    {
        if ($authUser) {
            return $authUser;
        }

        $email = $payload['email'];

        /** @var User|null $user */
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => trim(($payload['first_name'] ?? '').' '.($payload['last_name'] ?? '')),
                'email' => $email,
                'password' => bcrypt(str()->random(32)),
            ]);

            // TODO: assign customer role via Spatie roles, e.g. $user->assignRole('customer');
        }

        return $user;
    }

    /**
     * Resolve unit price for given variant+product+currency from catalog tables.
     *
     * 1. Try product_variant_prices
     * 2. Fallback to product_prices
     * 3. If none, throw
     */
    protected function resolveUnitPrice(int $variantId, int $productId, string $currencyCode): float
    {
        /** @var ProductVariantPrice|null $vp */
        $vp = ProductVariantPrice::where('product_variant_id', $variantId)
            ->where('currency_code', $currencyCode)
            ->first();

        if ($vp) {
            return (float) $vp->amount;
        }

        /** @var ProductPrice|null $pp */
        $pp = ProductPrice::where('product_id', $productId)
            ->where('currency_code', $currencyCode)
            ->first();

        if ($pp) {
            return (float) $pp->amount;
        }

        throw new RuntimeException('Price not configured for this product/variant in currency '.$currencyCode);
    }

    /**
     * Generate an order number using unix timestamp with retry on collision.
     */
    protected function generateOrderNumber(): int
    {
        $number = now()->timestamp;

        while (Order::where('number', $number)->exists()) {
            $number++;
        }

        return $number;
    }
}
