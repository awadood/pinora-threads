<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockBatch Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $stock_id
 * @property int $variant_id
 * @property string $received_at
 * @property string $currency_code
 * @property string $unit_cost
 * @property int $qty_received
 * @property int $qty_remaining
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Currency $currency
 * @property-read \App\Models\Stock $stock
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\StockBatchFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereQtyReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereQtyRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereStockId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBatch whereVariantId($value)
 *
 * @mixin \Eloquent
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
