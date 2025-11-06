<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * CustomerGroup Eloquent model.
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
        return $this->belongsToMany(User::class, 'customer_group_user');
    }
}
