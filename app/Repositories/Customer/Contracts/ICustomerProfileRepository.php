<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;

/**
 * ICustomerProfileRepository
 *
 * Repository contract for managing customer profile records.
 * Provides access helpers for the currently authenticated user
 * and admin-level lookup by user id.
 *
 * @author Abdul Wadood
 */
interface ICustomerProfileRepository extends IBaseRepository
{
    /**
     * Find a profile by the owning user id.
     */
    public function findByUserId(int $userId): ?\App\Models\CustomerProfile;
}
