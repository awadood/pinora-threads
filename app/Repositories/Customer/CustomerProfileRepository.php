<?php

namespace App\Repositories\Customer;

use App\Models\CustomerProfile;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\ICustomerProfileRepository;

/**
 * CustomerProfileRepository
 *
 * Eloquent implementation for customer profiles.
 *
 * @author Abdul Wadood
 */
class CustomerProfileRepository extends BaseRepository implements ICustomerProfileRepository
{
    protected string $modelClass = CustomerProfile::class;

    public function findByUserId(int $userId): ?CustomerProfile
    {
        return $this->query()->where('user_id', $userId)->first();
    }
}
