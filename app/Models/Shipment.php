<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks outbound fulfillment details, carrier data, and shipping costs.
 *
 * @author Abdul Wadood
 */
class Shipment extends AbstractLoggableModel
{
    protected $fillable = [
        'order_id',
        'stock_id',
        'shipment_method_code',
        'carrier',
        'tracking_number',
        'tracking_url',
        'shipment_status_code',
        'currency_code',
        'shipping_charge',
        'shipping_cost',
        'shipping_tax',
        'shipped_at',
        'delivered_at',
        'returned_at',
        'label_url',
        'carrier_payload',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'carrier_payload' => 'array',
            'notes' => 'array',
        ];
    }

    // Lifecycle

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShipmentMethod::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ShipmentStatus::class);
    }
}
