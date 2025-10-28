<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Bill
 *
 * @author Abdul Wadood
 */
class Bill extends AbstractModel
{
    protected $fillable = [
        'billing_date',
        'tx_year_month',
        'subtotal',
        'discount',
        'promotion',
        'tax',
        'previous_balance',
        'total',
        'cash',
        'credit_card',
        'debit_card',
        'balance',
        'adjust_balance',
        'is_canceled',
        'is_closed',
        'notes',
        'user_name',
        'bookings_serialized',
        'products_serialized',
        'discount_serialized',
        'promotions_serialized',
        'taxes_serialized',
        'vouchers_serialized',
        'package_serialized',
        'package_id',
        'customer_id',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'debit' => 'float',
            'credit' => 'float',
            'subtotal' => 'float',
            'discount' => 'float',
            'promotion' => 'float',
            'tax' => 'float',
            'previous_balance' => 'float',
            'total' => 'float',
            'cash' => 'float',
            'credit_card' => 'float',
            'debit_card' => 'float',
            'balance' => 'float',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(BillRefund::class);
    }
}
