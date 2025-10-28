<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Product
 *
 * @author Abdul Wadood
 */
class Product extends AbstractModel
{
    protected $fillable = [
        'name',
        'barcode',
        'size',
        'color',
        'usage',
        'company',
        'company_contact',
        'group_id',
        'client_id',
    ];

    // Relationships

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'group_id');
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(ProductStockItem::class);
    }

    public function stockItemStatus(): HasOne
    {
        return $this->hasOne(ProductStockItemStatus::class, '', '');
    }
}
