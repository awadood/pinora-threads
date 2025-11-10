<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Captures gateway attempts and states for auth/capture flows.
 *
 * @author Abdul Wadood
 */
class PaymentAttempt extends AbstractModel
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'currency_code',
        'method',
        'action',
        'status',
        'amount',
        'error_code',
        'error_message',
        'idempotency_key',
        'remote_ip',
        'request_payload',
        'response_payload',
        'attempted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
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
