<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A curated merchandising item linked to a section.
 *
 * For item_type="attribute", item_id references attribute_options.id.
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
