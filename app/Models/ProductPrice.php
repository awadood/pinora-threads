<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductPrice Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $product_id
 * @property string $currency_code
 * @property string $amount
 * @property string|null $compare_at
 * @property-read \App\Models\Currency $currency
 * @property-read \App\Models\Product $product
 *
 * @method static \Database\Factories\ProductPriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereCompareAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereProductId($value)
 *
 * @mixin \Eloquent
 */
class ProductPrice extends AbstractModel
{
    protected $fillable = [
        'product_id',
        'currency_code',
        'amount',
        'compare_at',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
