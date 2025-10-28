<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BillRefund
 *
 * @author Abdul Wadood
 */
class BillRefund extends AbstractModel
{
    protected $fillable = [
        'refund_date',
        'subtotal',
        'discount',
        'promotion',
        'tax',
        'total_refunded',
        'notes',
        'user_name',
        'booking_serialized',
        'discount_serialized',
        'promotion_serialized',
        'product_serialized',
        'tax_serialized',
        'package_serialized',
        'bill_id',
        'package_id',
        'user_id',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
