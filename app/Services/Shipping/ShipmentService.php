<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\Shipment;
use App\Repositories\Shipping\Contracts\IShipmentRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * ShipmentService
 *
 * Orchestrates shipping lifecycle:
 *  - Creating shipment for an order.
 *  - Updating shipment details.
 *  - Changing shipment status & timelines.
 *  - Resolving customer-owned shipments.
 *
 * @author Abdul Wadood
 */
class ShipmentService
{
    public function __construct(
        protected IShipmentRepository $shipments
    ) {}

    /**
     * Create a shipment for a given order.
     *
     * Enforces one shipment per order.
     * Copies currency & shipping_charge snapshot from order.
     *
     * @param  array<string,mixed>  $data
     */
    public function createForOrder(Order $order, array $data): Shipment
    {
        if ($this->shipments->findByOrderId($order->id)) {
            throw new InvalidArgumentException('Shipment already exists for this order.');
        }

        return DB::transaction(function () use ($order, $data) {
            $attributes = [
                'order_id' => $order->id,
                'stock_id' => $data['stock_id'],
                'shipment_method_code' => $data['shipment_method_code'],
                'carrier' => $data['carrier'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'tracking_url' => $data['tracking_url'] ?? null,
                'shipment_status_code' => $data['shipment_status_code'] ?? 'pending',
                'currency_code' => $order->currency_code,
                'shipping_charge' => $order->total_shipping,
                'shipping_cost' => $data['shipping_cost'],
                'shipping_tax' => $data['shipping_tax'],
                'shipped_at' => null,
                'delivered_at' => null,
                'returned_at' => null,
                'label_url' => $data['label_url'] ?? null,
                'carrier_payload' => $data['carrier_payload'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            /** @var Shipment $shipment */
            $shipment = $this->shipments->create($attributes);

            // Keep order's denormalized shipping fields in sync
            $order->shipping_method = $shipment->shipment_method_code;
            $order->tracking_number = $shipment->tracking_number;
            $order->carrier = $shipment->carrier;
            $order->shipment = [
                'id' => $shipment->id,
                'status' => $shipment->shipment_status_code,
                'method' => $shipment->shipment_method_code,
                'carrier' => $shipment->carrier,
                'tracking_number' => $shipment->tracking_number,
                'tracking_url' => $shipment->tracking_url,
            ];
            $order->save();

            return $shipment;
        });
    }

    /**
     * Update shipment details (carrier, tracking, costs, notes).
     *
     * @param  array<string,mixed>  $data
     */
    public function updateShipment(Shipment $shipment, array $data): Shipment
    {
        return DB::transaction(function () use ($shipment, $data) {
            $shipment->fill([
                'stock_id' => $data['stock_id'] ?? $shipment->stock_id,
                'shipment_method_code' => $data['shipment_method_code'] ?? $shipment->shipment_method_code,
                'carrier' => $data['carrier'] ?? $shipment->carrier,
                'tracking_number' => $data['tracking_number'] ?? $shipment->tracking_number,
                'tracking_url' => $data['tracking_url'] ?? $shipment->tracking_url,
                'shipping_cost' => $data['shipping_cost'] ?? $shipment->shipping_cost,
                'shipping_tax' => $data['shipping_tax'] ?? $shipment->shipping_tax,
                'label_url' => $data['label_url'] ?? $shipment->label_url,
                'carrier_payload' => $data['carrier_payload'] ?? $shipment->carrier_payload,
                'notes' => $data['notes'] ?? $shipment->notes,
            ])->save();

            // Sync order denormalized fields
            $order = $shipment->order;
            if ($order) {
                $order->shipping_method = $shipment->shipment_method_code;
                $order->tracking_number = $shipment->tracking_number;
                $order->carrier = $shipment->carrier;
                $order->shipment = [
                    'id' => $shipment->id,
                    'status' => $shipment->shipment_status_code,
                    'method' => $shipment->shipment_method_code,
                    'carrier' => $shipment->carrier,
                    'tracking_number' => $shipment->tracking_number,
                    'tracking_url' => $shipment->tracking_url,
                ];
                $order->save();
            }

            return $shipment->fresh();
        });
    }

    /**
     * Update shipment status and corresponding timelines.
     */
    public function updateStatus(Shipment $shipment, string $statusCode): Shipment
    {
        return DB::transaction(function () use ($shipment, $statusCode) {
            $shipment->shipment_status_code = $statusCode;

            $now = now();

            if ($statusCode === 'shipped') {
                $shipment->shipped_at = $shipment->shipped_at ?? $now;
            } elseif ($statusCode === 'delivered') {
                $shipment->delivered_at = $shipment->delivered_at ?? $now;
            } elseif ($statusCode === 'returned') {
                $shipment->returned_at = $shipment->returned_at ?? $now;
            }

            $shipment->save();

            $order = $shipment->order;
            if ($order) {
                if ($statusCode === 'shipped') {
                    $order->shipped_at = $order->shipped_at ?? $shipment->shipped_at;
                } elseif ($statusCode === 'delivered') {
                    $order->delivered_at = $order->delivered_at ?? $shipment->delivered_at;
                } elseif ($statusCode === 'returned') {
                    $order->cancelled_at = $order->cancelled_at ?? $now;
                }

                $order->shipment = [
                    'id' => $shipment->id,
                    'status' => $shipment->shipment_status_code,
                    'method' => $shipment->shipment_method_code,
                    'carrier' => $shipment->carrier,
                    'tracking_number' => $shipment->tracking_number,
                    'tracking_url' => $shipment->tracking_url,
                ];
                $order->save();
            }

            return $shipment->fresh();
        });
    }

    /**
     * Get shipment for a customer-owned order (or null).
     */
    public function findCustomerShipmentForOrder(int $userId, Order $order): ?Shipment
    {
        if ($order->user_id !== $userId) {
            return null;
        }

        return $this->shipments->findByOrderId($order->id);
    }
}
