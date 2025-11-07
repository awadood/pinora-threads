<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Attribute Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property string $code
 * @property string $label
 * @property string $type
 * @property bool $active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AttributeOption> $options
 * @property-read int|null $options_count
 *
 * @method static \Database\Factories\AttributeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereType($value)
 *
 * @mixin \Eloquent
 */
class Attribute extends AbstractModel
{
    protected $fillable = [
        'code',
        'label',
        'type',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }
}
