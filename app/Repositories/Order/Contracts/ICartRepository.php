<?php

namespace App\Repositories\Order\Contracts;

use App\Models\Cart;
use App\Repositories\IBaseRepository;

/**
 * ICartRepository
 *
 * Repository contract for Cart aggregate.
 *
 * @author Abdul Wadood
 */
interface ICartRepository extends IBaseRepository
{
    public function findByCookieKey(string $cookieKey): ?Cart;

    public function createForKey(string $cookieKey, string $currencyCode, ?int $userId = null): Cart;
}
