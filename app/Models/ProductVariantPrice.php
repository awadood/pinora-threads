<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductVariantPrice Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $product_variant_id
 * @property string $currency_code
 * @property string $amount
 * @property string|null $compare_at
 * @property-read \App\Models\Currency $currency
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\ProductVariantPriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantPrice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantPrice whereCompareAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantPrice whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantPrice whereProductVariantId($value)
 *
 * @mixin \Eloquent
 */
class ProductVariantPrice extends AbstractModel
{
    protected $fillable = [
        'product_variant_id',
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

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
