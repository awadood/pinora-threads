<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * CustomerGroup Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $sort_order
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\CustomerGroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
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
