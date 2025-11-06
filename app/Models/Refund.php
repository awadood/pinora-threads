<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Refund Eloquent model.
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
        'status',
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
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
