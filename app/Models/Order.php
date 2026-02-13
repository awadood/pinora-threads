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
    const CLAIM_STATUS_NEW = 'new';

    const CLAIM_STATUS_PENDING = 'pending';

    const CLAIM_STATUS_CLAIMED = 'claimed';

    protected $fillable = [
        'number',
        'user_id',
        'cart_id',
        'guest_token',
        'currency_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'claim_status',
        'order_status_code',
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
        return $this->belongsTo(Currency::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
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
