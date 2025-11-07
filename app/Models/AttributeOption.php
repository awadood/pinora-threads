<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AttributeOption Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $attribute_id
 * @property string $value
 * @property int $sort
 * @property-read \App\Models\Attribute $attribute
 *
 * @method static \Database\Factories\AttributeOptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeOption whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeOption whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeOption whereValue($value)
 *
 * @mixin \Eloquent
 */
class AttributeOption extends AbstractModel
{
    protected $fillable = [
        'attribute_id',
        'value',
        'sort',
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

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
