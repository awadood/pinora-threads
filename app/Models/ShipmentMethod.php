<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a shipment fulfillment method (e.g., courier, pickup, lmd partner).
 *
 * @author Abdul Wadood
 */
class ShipmentMethod extends AbstractModel
{
    const PICKUP = 'pickup';

    const SELF = 'self';

    const COURIER = 'courier';

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
