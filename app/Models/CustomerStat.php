<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aggregated customer lifecycle and revenue metrics.
 *
 * @author Abdul Wadood
 */
class CustomerStat extends AbstractLoggableModel
{
    protected $table = 'customer_stats';

    protected $fillable = [
        'user_id',
        'total_orders_count',
        'completed_orders_count',
        'cancelled_orders_count',
        'returned_orders_count',
        'refunded_orders_count',
        'first_order_at',
        'last_order_at',
        'gross_revenue',
        'net_revenue',
        'average_order_value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_orders_count' => 'integer',
            'completed_orders_count' => 'integer',
            'cancelled_orders_count' => 'integer',
            'returned_orders_count' => 'integer',
            'refunded_orders_count' => 'integer',
            'first_order_at' => 'datetime',
            'last_order_at' => 'datetime',
            'gross_revenue' => 'decimal:2',
            'net_revenue' => 'decimal:2',
            'average_order_value' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
