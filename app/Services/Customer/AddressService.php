<?php

namespace App\Services\Customer;

use App\Models\Address;
use App\Repositories\Customer\Contracts\IAddressRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * AddressService
 *
 * Encapsulates address CRUD operations and default flag handling
 * for a given user.
 *
 * @author Abdul Wadood
 */
class AddressService
{
    public function __construct(
        protected IAddressRepository $addresses
    ) {}

    /**
     * @return \Illuminate\Support\Collection<int, Address>
     */
    public function listForUser(Authenticatable $user)
    {
        return $this->addresses->forUser($user->getAuthIdentifier());
    }

    public function createForUser(Authenticatable $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data): Address {
            $data['user_id'] = $user->getAuthIdentifier();

            $address = $this->addresses->create($data);

            if (! empty($data['default_shipping'])) {
                $this->setDefaultShipping($user, $address);
            }

            if (! empty($data['default_billing'])) {
                $this->setDefaultBilling($user, $address);
            }

            return $address;
        });
    }

    public function updateForUser(Authenticatable $user, Address $address, array $data): Address
    {
        return DB::transaction(function () use ($user, $address, $data): Address {
            if ($address->user_id !== $user->getAuthIdentifier()) {
                abort(403);
            }

            $address->fill($data)->save();

            if (array_key_exists('default_shipping', $data) && $data['default_shipping']) {
                $this->setDefaultShipping($user, $address);
            }

            if (array_key_exists('default_billing', $data) && $data['default_billing']) {
                $this->setDefaultBilling($user, $address);
            }

            return $address;
        });
    }

    public function deleteForUser(Authenticatable $user, Address $address): void
    {
        if ($address->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        $this->addresses->destroy($address->getKey());
    }

    public function setDefaultShipping(Authenticatable $user, Address $address): void
    {
        if ($address->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        DB::transaction(function () use ($user, $address): void {
            $this->addresses->clearDefaults($user->getAuthIdentifier(), 'shipping');
            $address->forceFill(['default_shipping' => true])->save();
        });
    }

    public function setDefaultBilling(Authenticatable $user, Address $address): void
    {
        if ($address->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        DB::transaction(function () use ($user, $address): void {
            $this->addresses->clearDefaults($user->getAuthIdentifier(), 'billing');
            $address->forceFill(['default_billing' => true])->save();
        });
    }
}
