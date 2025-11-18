<?php

namespace App\Repositories\Order\Contracts;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\IBaseRepository;

/**
 * IOrderRepository
 *
 * Repository contract for Orders.
 *
 * @author Abdul Wadood
 */
interface IOrderRepository extends IBaseRepository
{
    public function createOrder(array $attributes): Order;

    public function createOrderItem(Order $order, array $attributes): OrderItem;
}
