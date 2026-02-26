<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Represents a selectable value for an attribute (e.g., Red, XL).
 *
 * @author Abdul Wadood
 */
class AttributeOption extends AbstractLoggableModel
{
    use HasMedia;

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

    public function thumbnailMedia(): MorphOne
    {
        return $this->primaryMediaForRole('thumbnail');
    }
}
