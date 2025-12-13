<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Auditable inventory movement (receipt, reservation, shipment, adjustment).
 *
 * @author Abdul Wadood
 */
class StockMovement extends AbstractLoggableModel
{
    protected $fillable = [
        'stock_id',
        'variant_id',
        'stock_movement_type_code',
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

    public function stockMovementType(): BelongsTo
    {
        return $this->belongsTo(StockMovementType::class);
    }
}
