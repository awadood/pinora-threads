<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Refund Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $order_id
 * @property int $payment_id
 * @property string $currency_code
 * @property string $amount
 * @property string $status
 * @property string|null $gateway_refund_id
 * @property string|null $reason
 * @property string|null $processed_at
 * @property string|null $idempotency_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Currency $currency
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Payment $payment
 *
 * @method static \Database\Factories\RefundFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereGatewayRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereIdempotencyKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Refund extends AbstractLoggableModel
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'currency_code',
        'amount',
        'status',
        'gateway_refund_id',
        'reason',
        'processed_at',
        'idempotency_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    // Lifecycle

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
