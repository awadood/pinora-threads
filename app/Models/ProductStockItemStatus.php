<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductStockItemStatus
 *
 * @author Abdul Wadood
 */
class ProductStockItemStatus extends AbstractModel
{
    public const IN_STOCK = 'IN_STOCK';

    public const LOW_STOCK = 'LOW_STOCK';

    public const OUT_OF_STOCK = 'OUT_OF_STOCK';

    protected $fillable = [
        'in_stock',
        'low_stock',
        'cost_price',
        'retail_price',
        'sales_commission',
        'notes',
        'quantity',
        'notify_stock_quantity',
        'product_stock_id',
        'product_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'float',
            'retail_price' => 'float',
            'sales_commission' => 'float',
        ];
    }

    public $timestamps = false;

    // Relationships

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
