<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a shipment lifecycle state (e.g., ready, in_transit, delivered, returned).
 *
 * @author Abdul Wadood
 */
class ShipmentStatus extends AbstractModel
{
    const PENDING = 'pending';

    const OUT_FOR_DELIVERY = 'out_for_delivery';

    const IN_TRANSIT = 'in_transit';

    const DELIVERED = 'delivered';

    const RETURNED = 'returned';

    const CANCELLED = 'cancelled';

    const FAILED = 'failed';

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

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
