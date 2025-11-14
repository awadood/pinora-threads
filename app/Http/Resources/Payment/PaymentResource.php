<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PaymentResource
 *
 * @author Abdul Wadood
 */
class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'invoice_id' => $this->invoice_id,
            'currency_code' => $this->currency_code,
            'payment_method_code' => $this->payment_method_code,
            'action' => $this->action,
            'payment_status_code' => $this->payment_status_code,
            'amount' => $this->amount,
            'gateway_txn_id' => $this->gateway_txn_id,
            'idempotency_key' => $this->idempotency_key,
            'processed_at' => $this->processed_at,
            'request_payload' => $this->request_payload,
            'response_payload' => $this->response_payload,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
