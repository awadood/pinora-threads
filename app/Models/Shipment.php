<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shipment Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $order_id
 * @property int $stock_id
 * @property string $method
 * @property string|null $carrier
 * @property string|null $tracking_number
 * @property string|null $tracking_url
 * @property string $status
 * @property string $currency_code
 * @property string $shipping_charge what customer paid (snapshot from order.total_shipping)
 * @property string $shipping_cost what we paid the carrier / internal cost
 * @property string $shipping_tax tax owed on shipping, if applicable (US-state rules)
 * @property string|null $shipped_at
 * @property string|null $delivered_at
 * @property string|null $returned_at
 * @property string|null $label_url
 * @property array<array-key, mixed>|null $carrier_payload
 * @property array<array-key, mixed>|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Currency $currency
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Stock $stock
 *
 * @method static \Database\Factories\ShipmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereCarrier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereCarrierPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereLabelUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereReturnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippingCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippingTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereStockId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereTrackingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Shipment extends AbstractLoggableModel
{
    protected $fillable = [
        'order_id',
        'stock_id',
        'method',
        'carrier',
        'tracking_number',
        'tracking_url',
        'status',
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
}
