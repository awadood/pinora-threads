<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a billable document for an order with balance tracking.
 *
 * @author Abdul Wadood
 */
class Invoice extends AbstractLoggableModel
{
    protected $fillable = [
        'order_id',
        'number',
        'currency_code',
        'amount_due',
        'status',
        'issued_at',
        'due_at',
        'paid_at',
        'meta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    // Lifecycle

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
