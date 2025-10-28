<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProductGroup
 *
 * @author Abdul Wadood
 */
class ProductGroup extends AbstractModel
{
    protected $fillable = [
        'name',
        'default',
        'active',
    ];

    // Relationships

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'group_id');
    }
}
