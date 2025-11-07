<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockLevel Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $stock_id
 * @property int $variant_id
 * @property int $quantity
 * @property int $notify_below
 * @property bool $allow_backorder Can we sell it now even if out of stock?
 * @property string|null $promised_at When can the buyer expect it?
 * @property string|null $restock_eta When do we expect to receive it?
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Stock $stock
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\StockLevelFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereAllowBackorder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereNotifyBelow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel wherePromisedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereRestockEta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereStockId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLevel whereVariantId($value)
 *
 * @mixin \Eloquent
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
