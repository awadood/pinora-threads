<?php

namespace App\Repositories\Customer;

use App\Models\Address;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\IAddressRepository;
use Illuminate\Support\Collection;

/**
 * AddressRepository
 *
 * Eloquent implementation for customer addresses.
 *
 * @author Abdul Wadood
 */
class AddressRepository extends BaseRepository implements IAddressRepository
{
    protected string $modelClass = Address::class;

    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function clearDefaults(int $userId, ?string $type = null): void
    {
        $query = $this->query()->where('user_id', $userId);

        if ($type === 'shipping' || $type === null) {
            $query->update(['default_shipping' => false]);
        }

        if ($type === 'billing' || $type === null) {
            $this->query()
                ->where('user_id', $userId)
                ->update(['default_billing' => false]);
        }
    }
}
