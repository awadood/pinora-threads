<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Records a placed purchase with totals, status, and fulfillment lifecycle.
 *
 * @author Abdul Wadood
 */
class Order extends AbstractLoggableModel
{
    protected $fillable = [
        'number',
        'user_id',
        'currency_code',
        'status',
        'billing_address_id',
        'shipping_address_id',
        'shipping_address',
        'billing_address',
        'tax_inclusive',
        'items_subtotal',
        'total_discount',
        'total_tax',
        'total_shipping',
        'total',
        'discount',
        'shipment',
        'promotions',
        'taxes',
        'payment_method',
        'payment_txn_id',
        'idempotency_key',
        'shipping_method',
        'tracking_number',
        'carrier',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'refunded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tax_inclusive' => 'boolean',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'discount' => 'array',
            'shipment' => 'array',
            'promotions' => 'array',
            'taxes' => 'array',
        ];
    }

    // Lifecycle

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function shipmentRecord(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
