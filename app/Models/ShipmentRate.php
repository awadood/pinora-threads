<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ShipmentRate
 *
 * Defines shipping price tiers for a shipment method and currency.
 */
class ShipmentRate extends AbstractLoggableModel
{
    protected $fillable = [
        'shipment_method_code',
        'currency_code',
        'min_subtotal',
        'max_subtotal',
        'price',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_subtotal' => 'float',
            'max_subtotal' => 'float',
            'price' => 'float',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShipmentMethod::class, 'shipment_method_code', 'code');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
