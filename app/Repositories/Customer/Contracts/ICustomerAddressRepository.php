<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

/**
 * ICustomerAddressRepository
 *
 * Repository contract for managing customer saved addresses.
 * Supports listing addresses for a given user.
 *
 * @author Abdul Wadood
 */
interface ICustomerAddressRepository extends IBaseRepository
{
    /**
     * Get all addresses for a user.
     *
     * @return Collection<int, \App\Models\CustomerAddress>
     */
    public function forUser(int $userId): Collection;
}
