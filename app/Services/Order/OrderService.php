<?php

namespace App\Services\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductPrice;
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

            $cart->load([
                'items.product.attributes.attribute',
                'items.product.attributes.option',
                'items.product.thumbnailMedia.asset',
                'items.product.heroMedia.asset',
                'items.product.ogImageMedia.asset',
            ]);

            $orderItemsData = [];

            foreach ($cart->items as $item) {
                $product = $item->product;

                $unitPrice = $this->resolveUnitPrice($product->id, $currency);

                $lineSubtotal = $unitPrice * $item->quantity;
                $lineDiscount = 0.00;
                $lineTax = 0.00;
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $itemsSubtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;
                $totalTax += $lineTax;

                $attributesSnapshot = $product->attributes->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->attribute_id,
                        'code' => $attr->attribute?->code,
                        'label' => $attr->attribute?->label,
                        'option_id' => $attr->option_id,
                        'value' => $attr->option?->value ?? $attr->value,
                    ];
                })->values()->all();

                $productSnapshot = [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'type' => $product->type,
                    'description' => $product->description,
                    'tax_class_id' => $product->tax_class_id,
                    'attributes' => $attributesSnapshot,
                    'media' => [
                        'thumbnail' => $product->thumbnailMedia?->asset?->urlFor('thumb_sm'),
                        'hero' => $product->heroMedia?->asset?->urlFor('thumb_md'),
                        'og_image' => $product->ogImageMedia?->asset?->urlFor('thumb_md'),
                    ],
                ];

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'product' => $productSnapshot,
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
     * Resolve unit price for given product+currency from catalog tables.
     *
     * 1. Try product_prices
     * 2. If none, throw
     */
    protected function resolveUnitPrice(int $productId, string $currencyCode): float
    {
        /** @var ProductPrice|null $pp */
        $pp = ProductPrice::where('product_id', $productId)
            ->where('currency_code', $currencyCode)
            ->first();

        if ($pp) {
            return (float) $pp->amount;
        }

        throw new RuntimeException('Price not configured for this product in currency '.$currencyCode);
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

    protected function initiatePayment(Order $order): void
    {
        // TODO (Payment domain):
        // - Create a payment intent with the selected gateway (Stripe, etc.)
        // - Attach gateway transaction ID to $order->payment_txn_id
        // - Possibly redirect or return client_secret to frontend.
    }

    protected function prepareShipment(Order $order): void
    {
        // TODO (Shipping domain):
        // - Use shipping address and items to compute shipment details
        // - Add carrier / tracking info once available
        // - Update $order->shipment JSON snapshot
    }
    // App\Services\Order\OrderService

    protected function initiatePaymentFlow(Order $order): void
    {
        // TODO: Payment domain
        // - e.g. PaymentService::createIntent($order)
        // - Set $order->payment_method and $order->payment_txn_id
        // - Handle idempotency via $order->idempotency_key
    }

    protected function prepareShipmentDetails(Order $order): void
    {
        // TODO: Shipping domain
        // - e.g. ShippingService::estimate($order->shipping_address, $order->items)
        // - Populate $order->shipment JSON with carrier, service code, cost, ETA
    }
}
