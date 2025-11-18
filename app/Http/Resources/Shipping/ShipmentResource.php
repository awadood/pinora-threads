<?php

namespace App\Http\Resources\Shipping;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ShipmentResource
 *
 * @author Abdul Wadood
 */
class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'stock_id' => $this->stock_id,
            'shipment_method_code' => $this->shipment_method_code,
            'carrier' => $this->carrier,
            'tracking_number' => $this->tracking_number,
            'tracking_url' => $this->tracking_url,
            'shipment_status_code' => $this->shipment_status_code,
            'currency_code' => $this->currency_code,
            'shipping_charge' => (float) $this->shipping_charge,
            'shipping_cost' => (float) $this->shipping_cost,
            'shipping_tax' => (float) $this->shipping_tax,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'returned_at' => $this->returned_at,
            'label_url' => $this->label_url,
            'carrier_payload' => $this->carrier_payload,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
