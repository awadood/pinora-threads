<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProductStock
 *
 * @author Abdul Wadood
 */
class ProductStock extends AbstractModel
{
    protected $fillable = [
        'title',
    ];

    public $timestamps = false;

    // Relationships

    public function items(): HasMany
    {
        return $this->hasMany(ProductStockItem::class);
    }

    public function itemStatuses(): HasMany
    {
        return $this->hasMany(ProductStockItemStatus::class);
    }
}
