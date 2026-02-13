<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\ResolvesCart;
use Illuminate\Http\Request;

/**
 * CartController
 *
 * Handles cart operations for both guests and authenticated users.
 * Cart is identified by X-Cart-Key header (or cart_key cookie) plus optional user.
 *
 * @author Abdul Wadood
 */
class CartController extends Controller
{
    use ResolvesCart;

    /**
     * GET /api/cart
     *
     * Returns the current cart with items. If no cart exists for the given key,
     * a fresh cart is created and returned. Frontend must store the returned
     * cookie_key and send it as X-Cart-Key for subsequent requests.
     */
    public function show(Request $request): CartResource
    {
        $cart = $this->resolveCart($request);

        $this->loadCartForResponse($cart);

        return CartResource::make($cart);
    }

    /**
     * POST /api/cart/items
     *
     * Body: { "product_id": 123, "quantity": 2 }
     */
    public function addItem(Request $request): CartResource
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->resolveCart($request);

        /** @var Product $product */
        $product = Product::findOrFail($data['product_id']);

        /** @var CartItem|null $item */
        $item = $cart->items()
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->quantity += $data['quantity'];
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
            ]);
        }

        $cart->refresh();
        $this->loadCartForResponse($cart);

        return CartResource::make($cart);
    }

    /**
     * PUT /api/cart/items/{item}
     *
     * Body: { "quantity": 3 }
     */
    public function updateItem(Request $request, CartItem $item): CartResource
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = $this->resolveCart($request);

        if ($item->cart_id !== $cart->id) {
            abort(404);
        }

        if ($data['quantity'] === 0) {
            $item->delete();
        } else {
            $item->quantity = $data['quantity'];
            $item->save();
        }

        $cart->refresh();
        $this->loadCartForResponse($cart);

        return CartResource::make($cart);
    }

    /**
     * DELETE /api/cart/items/{item}
     */
    public function removeItem(Request $request, CartItem $item): CartResource
    {
        $cart = $this->resolveCart($request);

        if ($item->cart_id !== $cart->id) {
            abort(404);
        }

        $item->delete();

        $cart->refresh();
        $this->loadCartForResponse($cart);

        return CartResource::make($cart);
    }

    /**
     * DELETE /api/cart/clear
     */
    public function clear(Request $request): CartResource
    {
        $cart = $this->resolveCart($request);

        $cart->items()->delete();
        $cart->refresh();
        $this->loadCartForResponse($cart);

        return CartResource::make($cart);
    }

    private function loadCartForResponse(Cart $cart): void
    {
        $currencyCode = $cart->currency_code;

        $cart->load([
            'items.cart',
            'items.product' => function ($query) use ($currencyCode) {
                $query->with([
                    'prices' => fn ($priceQuery) => $priceQuery->where('currency_code', $currencyCode),
                    'thumbnailMedia.asset.renditions',
                ]);
            },
        ]);
    }
}
