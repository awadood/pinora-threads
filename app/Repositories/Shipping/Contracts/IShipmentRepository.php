<?php

namespace App\Repositories\Shipping\Contracts;

use App\Models\Shipment;
use App\Repositories\IBaseRepository;

/**
 * IShipmentRepository
 *
 * Repository contract for Shipment model operations.
 * Provides simple CRUD plus helpers for order-linked queries.
 *
 * @author Abdul Wadood
 */
interface IShipmentRepository extends IBaseRepository
{
    /**
     * Find shipment by order id.
     */
    public function findByOrderId(int $orderId): ?Shipment;
}
