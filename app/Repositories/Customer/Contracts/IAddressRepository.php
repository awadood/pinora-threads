<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

/**
 * IAddressRepository
 *
 * Repository contract for managing customer saved addresses.
 * Supports listing addresses for a given user and enforcing
 * a single default shipping/billing address per user.
 *
 * @author Abdul Wadood
 */
interface IAddressRepository extends IBaseRepository
{
    /**
     * Get all addresses for a user.
     *
     * @return Collection<int, \App\Models\Address>
     */
    public function forUser(int $userId): Collection;

    /**
     * Clear default_shipping/default_billing flags for a user.
     */
    public function clearDefaults(int $userId, ?string $type = null): void;
}
