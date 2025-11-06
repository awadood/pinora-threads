<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AttributeOption Eloquent model.
 *
 * @author Abdul Wadood
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
