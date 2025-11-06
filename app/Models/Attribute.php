<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Attribute Eloquent model.
 *
 * @author Abdul Wadood
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
