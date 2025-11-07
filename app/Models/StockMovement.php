<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockMovement Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $stock_id
 * @property int $variant_id
 * @property string $type
 * @property int $quantity_delta positive value for moving in and negative for moving out
 * @property int|null $stock_batch_id
 * @property int|null $order_id
 * @property int|null $performed_by
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\StockBatch|null $batch
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User|null $performedBy
 * @property-read \App\Models\Stock $stock
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\StockMovementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement wherePerformedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereQuantityDelta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereStockBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereStockId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereVariantId($value)
 *
 * @mixin \Eloquent
 */
class StockMovement extends AbstractModel
{
    protected $fillable = [
        'stock_id',
        'variant_id',
        'type',
        'quantity_delta',
        'stock_batch_id',
        'order_id',
        'performed_by',
        'reason',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
