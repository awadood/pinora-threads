<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\ShipmentMethod;
use App\Support\Storefront\StoreContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * ResolvesCart
 *
 * Shared trait to resolve or create a Cart based on X-Cart-Key header
 * (or cart_key cookie) and inferred currency.
 *
 * Currency resolution is currently simplified:
 *  - If X-Currency-Code is provided and valid, use it.
 *  - Else, map Cloudflare CF-IPCountry header to currency:
 *      PK -> PKR
 *      US -> USD
 *  - Else default to PKR.
 *
 * Frontend must store cookie_key from CartResource and send it back via
 * X-Cart-Key header on subsequent requests.
 *
 * @author Abdul Wadood
 */
trait ResolvesCart
{
    protected function resolveCart(Request $request): Cart
    {
        $user = $request->user();
        $cookieKey = $request->header('X-Cart-Key') ?? $request->cookie('cart_key');

        if (! $cookieKey) {
            $cookieKey = (string) Str::uuid();
        }

        /** @var Cart|null $cart */
        $cart = Cart::where('cookie_key', $cookieKey)->first();

        if (! $cart) {
            $currencyCode = $this->resolveCurrencyCode($request);

            $defaultShipping = ShipmentMethod::query()->where('code', ShipmentMethod::SELF)->where('active', true)->first();

            $cart = Cart::create([
                'user_id' => $user?->id,
                'cookie_key' => $cookieKey,
                'currency_code' => $currencyCode,
                'shipping_method_code' => $defaultShipping?->code,
            ]);
        } else {
            if ($user && ! $cart->user_id) {
                $cart->user_id = $user->id;
                $cart->save();
            }
        }

        return $cart;
    }

    protected function resolveCurrencyCode(Request $request): string
    {
        $ctx = $request->attributes->get('store_ctx');
        if ($ctx instanceof StoreContext && $ctx->currency) {
            return $ctx->currency;
        }

        $headerCurrency = $request->header('X-Currency-Code');
        if (in_array($headerCurrency, ['PKR', 'USD'], true)) {
            return $headerCurrency;
        }

        $country = $request->header('CF-IPCountry');

        return match ($country) {
            'PK' => 'PKR',
            'US' => 'USD',
            default => 'PKR',
        };
    }
}
