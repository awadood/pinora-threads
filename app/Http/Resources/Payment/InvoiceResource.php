<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * InvoiceResource
 *
 * @author Abdul Wadood
 */
class InvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'number' => $this->number,
            'currency_code' => $this->currency_code,
            'amount_due' => $this->amount_due,
            'invoice_status_code' => $this->invoice_status_code,
            'issued_at' => $this->issued_at,
            'due_at' => $this->due_at,
            'paid_at' => $this->paid_at,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
