<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A concrete SKU derived from a product’s attributes (size/color etc.).
 *
 * @author Abdul Wadood
 */
class MerchSectionItem extends AbstractLoggableModel
{
    protected $fillable = [
        'merch_section_id',
        'item_type',
        'item_id',
        'position',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(MerchSection::class, 'merch_section_id');
    }
}
