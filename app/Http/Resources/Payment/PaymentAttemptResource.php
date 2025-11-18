<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PaymentAttemptResource
 *
 * @author Abdul Wadood
 */
class PaymentAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'order_id' => (int) $this->order_id,
            'payment_id' => $this->payment_id === null ? null : (int) $this->payment_id,
            'currency_code' => $this->currency_code,
            'method' => $this->method,
            'action' => $this->action,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            'idempotency_key' => $this->idempotency_key,
            'remote_ip' => $this->remote_ip,
            'attempted_at' => $this->attempted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
