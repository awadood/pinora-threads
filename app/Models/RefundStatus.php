<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a refund processing state (e.g., requested, approved, rejected, completed).
 *
 * @author Abdul Wadood
 */
class RefundStatus extends AbstractLoggableModel
{
    const REQUESTED = 'requested';

    const APPROVED = 'approved';

    const PROCESSED = 'processed';

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

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
