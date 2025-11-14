<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Defines the semantic of a stock movement (e.g., receipt, sale, return, adjustment).
 *
 * @author Abdul Wadood
 */
class StockMovementType extends AbstractModel
{
    const PURCHASE = 'purchase';

    const SALE = 'sale';

    const REFUND = 'refund';

    const ADJUSTMENT = 'adjustment';

    const TRANSFER_IN = 'transfer_in';

    const TRANSFER_OUT = 'transfer_out';

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'active',
    ];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'type_code', 'code');
    }
}
