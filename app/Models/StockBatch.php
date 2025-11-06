<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockBatch Eloquent model.
 *
 * @author Abdul Wadood
 */
class StockBatch extends AbstractModel
{
    protected $fillable = [
        'stock_id',
        'variant_id',
        'received_at',
        'currency_code',
        'unit_cost',
        'qty_received',
        'qty_remaining',
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

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
