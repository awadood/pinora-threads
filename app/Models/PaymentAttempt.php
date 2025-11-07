<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PaymentAttempt Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $payment_id
 * @property string $currency_code
 * @property string $method
 * @property string $action
 * @property string $status
 * @property string $amount
 * @property string|null $error_code
 * @property string|null $error_message
 * @property string|null $idempotency_key
 * @property string|null $remote_ip
 * @property array<array-key, mixed>|null $request_payload
 * @property array<array-key, mixed>|null $response_payload
 * @property string $attempted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency $currency
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Payment|null $payment
 *
 * @method static \Database\Factories\PaymentAttemptFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereAttemptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereErrorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereIdempotencyKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereRemoteIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereRequestPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereResponsePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAttempt whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PaymentAttempt extends AbstractModel
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'currency_code',
        'method',
        'action',
        'status',
        'amount',
        'error_code',
        'error_message',
        'idempotency_key',
        'remote_ip',
        'request_payload',
        'response_payload',
        'attempted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
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
