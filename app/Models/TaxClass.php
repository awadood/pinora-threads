<?php

namespace App\Models;

/**
 * TaxClass Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\TaxClassFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxClass extends AbstractModel
{
    protected $fillable = [
        'name',
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
}
