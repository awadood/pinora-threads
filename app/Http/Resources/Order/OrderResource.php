<?php

namespace App\Http\Resources\Order;

use App\Services\Order\OrderClaimService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OrderResource
 *
 * @author Abdul Wadood
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        $links = app(OrderClaimService::class);

        return [
            'id' => $this->id,
            'number' => $this->number,
            'user_id' => $this->user_id,
            'currency_code' => $this->currency_code,
            'guest_token' => $this->guest_token,
            'claim_status' => $this->claim_status,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'order_status_code' => $this->order_status_code,
            'billing_address_id' => $this->billing_address_id,
            'shipping_address_id' => $this->shipping_address_id,
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'tax_inclusive' => $this->tax_inclusive,
            'items_subtotal' => (float) $this->items_subtotal,
            'total_discount' => (float) $this->total_discount,
            'total_tax' => (float) $this->total_tax,
            'total_shipping' => (float) $this->total_shipping,
            'total' => (float) $this->total,
            'discount' => $this->discount,
            'shipment' => $this->shipment,
            'promotions' => $this->promotions,
            'taxes' => $this->taxes,
            'payment_method' => $this->payment_method,
            'payment_txn_id' => $this->payment_txn_id,
            'shipping_method' => $this->shipping_method,
            'tracking_number' => $this->tracking_number,
            'carrier' => $this->carrier,
            'paid_at' => $this->paid_at,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'cancelled_at' => $this->cancelled_at,
            'refunded_at' => $this->refunded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tracking_url' => $links->buildTrackingUrl($this->resource),
            'claim_url' => $links->buildClaimUrl($this->resource),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
