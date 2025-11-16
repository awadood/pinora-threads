<?php

namespace App\Services\Customer;

use App\Models\CustomerProfile;
use App\Repositories\Customer\Contracts\ICustomerProfileRepository;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * CustomerProfileService
 *
 * Orchestrates read/write operations for customer profiles,
 * including privacy-safe access for the currently authenticated user.
 *
 * @author Abdul Wadood
 */
class CustomerProfileService
{
    public function __construct(
        protected ICustomerProfileRepository $profiles
    ) {}

    public function getOrCreateForUser(Authenticatable $user): CustomerProfile
    {
        $existing = $this->profiles->findByUserId($user->getAuthIdentifier());
        if ($existing) {
            return $existing;
        }

        return $this->profiles->create([
            'user_id' => $user->getAuthIdentifier(),
            'tax_class_id' => config('pinnora.defaults.customer_tax_class_id'),
            'marketing_email_opt_in' => false,
            'marketing_sms_opt_in' => false,
            'preferred_currency' => config('pinnora.defaults.currency', 'PKR'),
        ]);
    }

    public function updateForUser(Authenticatable $user, array $data): CustomerProfile
    {
        $profile = $this->getOrCreateForUser($user);
        $profile->fill($data)->save();

        return $profile;
    }
}
