<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\CartResource;
use App\Models\ShipmentMethod;
use App\Services\Order\ShipmentRateService;
use App\Support\ResolvesCart;
use Illuminate\Http\Request;

/**
 * CartShippingController
 *
 * Provides shipping method quotes for the current cart and allows selecting a method.
 */
class CartShippingController extends Controller
{
    use ResolvesCart;

    public function __construct(protected ShipmentRateService $shippingRates) {}

    /**
     * GET /api/cart/shipping-methods
     */
    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);

        $methods = $this->shippingRates->listForCart($cart);

        return response()->json($methods);
    }

    /**
     * PUT /api/cart/shipping-method
     * Body: { "shipment_method_code": "courier" }
     */
    public function update(Request $request): CartResource
    {
        $data = $request->validate([
            'shipment_method_code' => ['required', 'string', 'exists:shipment_methods,code'],
        ]);

        $cart = $this->resolveCart($request);

        /** @var ShipmentMethod $method */
        $method = ShipmentMethod::findOrFail($data['shipment_method_code']);

        if (! $method->active) {
            return abort(422, 'Selected shipping method is not available.');
        }

        $methods = $this->shippingRates->listForCart($cart);
        $selected = collect($methods)->firstWhere('code', $method->code);

        if (! $selected) {
            return abort(422, 'Selected shipping method has no valid rate for this cart.');
        }

        $cart->shipping_method_code = $method->code;
        $cart->save();

        $cart->refresh();
        $cart->load([
            'items.cart',
            'items.product' => function ($query) use ($cart) {
                $query->with([
                    'prices' => fn ($priceQuery) => $priceQuery->where('currency_code', $cart->currency_code),
                    'thumbnailMedia.asset.renditions',
                ]);
            },
        ]);

        return CartResource::make($cart);
    }
}
