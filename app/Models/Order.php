<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Order Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $number
 * @property int $user_id
 * @property string $currency_code
 * @property string $status
 * @property int|null $billing_address_id
 * @property int|null $shipping_address_id
 * @property array<array-key, mixed> $shipping_address
 * @property array<array-key, mixed> $billing_address
 * @property bool $tax_inclusive
 * @property string $items_subtotal
 * @property string $total_discount
 * @property string $total_tax
 * @property string $total_shipping
 * @property string $total
 * @property array<array-key, mixed>|null $discount
 * @property array<array-key, mixed>|null $shipment
 * @property array<array-key, mixed>|null $promotions
 * @property array<array-key, mixed>|null $taxes
 * @property string|null $payment_method
 * @property string|null $payment_txn_id
 * @property string|null $idempotency_key
 * @property string|null $shipping_method
 * @property string|null $tracking_number
 * @property string|null $carrier
 * @property string|null $paid_at
 * @property string|null $shipped_at
 * @property string|null $delivered_at
 * @property string|null $cancelled_at
 * @property string|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read \App\Models\Shipment|null $shipmentRecord
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCarrier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIdempotencyKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereItemsSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentTxnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePromotions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereRefundedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxInclusive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 *
 * @mixin \Eloquent
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
