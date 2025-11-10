<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Snapshot of on-hand, reserved, and available quantities for a SKU.
 *
 * @author Abdul Wadood
 */
class StockLevel extends AbstractModel
{
    protected $fillable = [
        'stock_id',
        'variant_id',
        'quantity',
        'notify_below',
        'allow_backorder',
        'promised_at',
        'restock_eta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allow_backorder' => 'boolean',
        ];
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
}
