<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductVariantAttribute Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $product_variant_id
 * @property int $attribute_id
 * @property int|null $option_id
 * @property string|null $value
 * @property-read \App\Models\Attribute $attribute
 * @property-read \App\Models\AttributeOption|null $option
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\ProductVariantAttributeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantAttribute whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantAttribute whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantAttribute whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantAttribute whereValue($value)
 *
 * @mixin \Eloquent
 */
class ProductVariantAttribute extends AbstractModel
{
    protected $fillable = [
        'product_variant_id',
        'attribute_id',
        'option_id',
        'value',
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

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AttributeOption::class, 'option_id');
    }
}
