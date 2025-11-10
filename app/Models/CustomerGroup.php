<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Segments customers for pricing rules, promotions, and campaigns.
 *
 * @author Abdul Wadood
 */
class CustomerGroup extends AbstractLoggableModel
{
    protected $fillable = [
        'name',
        'code',
        'active',
        'notes',
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
