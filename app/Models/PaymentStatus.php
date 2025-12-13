<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a payment processing state (e.g., pending, succeeded, failed, refunded).
 *
 * @author Abdul Wadood
 */
class PaymentStatus extends AbstractLoggableModel
{
    const PENDING = 'pending';

    const SUCCEEDED = 'succeeded';

    const FAILED = 'failed';

    const CANCELLED = 'cancelled';

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'active',
    ];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
