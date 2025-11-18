<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\BaseRepository;
use App\Repositories\Order\Contracts\IOrderRepository;

/**
 * OrderRepository
 *
 * @author Abdul Wadood
 */
class OrderRepository extends BaseRepository implements IOrderRepository
{
    protected string $modelClass = Order::class;

    public function __construct()
    {
        $this->allowedSearchColumns = [
            'user_id' => true,
            'currency_code' => true,
            'order_status_code' => true,
            'number' => true,
        ];
    }

    public function createOrder(array $attributes): Order
    {
        /** @var Order $order */
        $order = $this->query()->create($attributes);

        return $order;
    }

    public function createOrderItem(Order $order, array $attributes): OrderItem
    {
        /** @var OrderItem $item */
        $item = $order->items()->create($attributes);

        return $item;
    }
}
