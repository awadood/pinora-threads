<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RefundResource
 *
 * @author Abdul Wadood
 */
class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => (int) $this->order_id,
            'payment_id' => (int) $this->payment_id,
            'currency_code' => $this->currency_code,
            'amount' => (float) $this->amount,
            'refund_status_code' => $this->refund_status_code,
            'gateway_refund_id' => $this->gateway_refund_id,
            'reason' => $this->reason,
            'processed_at' => $this->processed_at,
            'idempotency_key' => $this->idempotency_key,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
