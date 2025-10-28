<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CustomerGroup
 *
 * @author Abdul Wadood
 */
class CustomerGroup extends AbstractModel
{
    protected $fillable = [
        'name',
        'default',
        'active',
        'client_id',
    ];

    // Relationships

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'group_id');
    }
}
