<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A purchased product line within an order.
 *
 * @author Abdul Wadood
 */
class OrderItem extends AbstractLoggableModel
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'sku',
        'product',
        'quantity',
        'unit_price',
        'subtotal',
        'discount',
        'tax',
        'total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product' => 'array',
        ];
    }

    // Lifecycle

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
