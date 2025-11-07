<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CustomerGroupUser Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property-read \App\Models\CustomerGroup|null $group
 * @property-read \App\Models\User|null $user
 *
 * @method static \Database\Factories\CustomerGroupUserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroupUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroupUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroupUser query()
 *
 * @mixin \Eloquent
 */
class CustomerGroupUser extends AbstractModel
{
    protected $fillable = [
        'customer_group_id',
        'user_id',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
