<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Groups received inventory into batches with lot/expiry metadata.
 *
 * @author Abdul Wadood
 */
class StockBatch extends AbstractLoggableModel
{
    protected $fillable = [
        'stock_id',
        'product_id',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'stock_batch_id');
    }
}
