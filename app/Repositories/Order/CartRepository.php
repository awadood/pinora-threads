<?php

namespace App\Repositories\Order;

use App\Models\Cart;
use App\Repositories\BaseRepository;
use App\Repositories\Order\Contracts\ICartRepository;

/**
 * CartRepository
 *
 * @author Abdul Wadood
 */
class CartRepository extends BaseRepository implements ICartRepository
{
    protected string $modelClass = Cart::class;

    public function __construct()
    {
        $this->allowedSearchColumns = [
            'user_id' => true,
            'cookie_key' => true,
            'currency_code' => true,
        ];
    }

    public function findByCookieKey(string $cookieKey): ?Cart
    {
        /** @var Cart|null $cart */
        $cart = $this->query()->where('cookie_key', $cookieKey)->first();

        return $cart;
    }

    public function createForKey(string $cookieKey, string $currencyCode, ?int $userId = null): Cart
    {
        /** @var Cart $cart */
        $cart = $this->query()->create([
            'user_id' => $userId,
            'cookie_key' => $cookieKey,
            'currency_code' => $currencyCode,
        ]);

        return $cart;
    }
}
