<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a returned amount against a payment or order with reasons.
 *
 * @author Abdul Wadood
 */
class Refund extends AbstractLoggableModel
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'currency_code',
        'amount',
        'refund_status_code',
        'gateway_refund_id',
        'reason',
        'processed_at',
        'idempotency_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    // Lifecycle

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(RefundStatus::class);
    }
}
