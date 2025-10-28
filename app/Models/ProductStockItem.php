<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductStockItem
 *
 * @author Abdul Wadood
 */
class ProductStockItem extends AbstractModel
{
    protected $fillable = [
        'cost_price',
        'retail_price',
        'sales_commission',
        'notes',
        'quantity',
        'notify_stock_quantity',
        'product_id',
        'product_stock_id',
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

    // Relationships

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }
}
