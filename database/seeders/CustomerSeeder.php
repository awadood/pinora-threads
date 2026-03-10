<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    private int $usUserId;

    private int $pkUserId;

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedUsersAndGroups();
            $this->seedAccountsAndCustomerAddresses();
        });
    }

    protected function seedUsersAndGroups(): void
    {
        $usUser = User::firstOrCreate(
            ['email' => 'us.customer@example.com'],
            [
                'name' => 'US Customer',
                'password' => Hash::make('password'),
                'phone' => fake()->numerify('300#######'),
                'active' => true,
            ]
        );
        $pkUser = User::firstOrCreate(
            ['email' => 'pk.customer@example.com'],
            [
                'name' => 'PK Customer',
                'password' => Hash::make('password'),
                'phone' => fake()->numerify('300#######'),
                'active' => true,
            ]
        );

        $this->usUserId = (int) $usUser->id;
        $this->pkUserId = (int) $pkUser->id;

        $standardId = DB::table('customer_groups')->where('code', 'standard')->value('id');
        $vipId = DB::table('customer_groups')->where('code', 'vip')->value('id');

        if ($standardId) {
            DB::table('customer_group_user')->updateOrInsert(
                ['customer_group_id' => $standardId, 'user_id' => $this->usUserId],
                []
            );
        }

        if ($vipId) {
            DB::table('customer_group_user')->updateOrInsert(
                ['customer_group_id' => $vipId, 'user_id' => $this->pkUserId],
                []
            );
        }
    }

    protected function seedAccountsAndCustomerAddresses(): void
    {
        // Customer addresses
        DB::table('customer_addresses')->updateOrInsert(
            ['user_id' => $this->usUserId, 'label' => 'Home'],
            [
                'name' => 'US Customer',
                'line1' => '123 Market St',
                'line2' => null,
                'city' => 'San Francisco',
                'state_code' => 'CA',
                'postal_code' => '94105',
                'country_code' => 'US',
                'phone' => '+14155550100',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('customer_addresses')->updateOrInsert(
            ['user_id' => $this->pkUserId, 'label' => 'Home'],
            [
                'name' => 'PK Customer',
                'line1' => 'House 10, Street 2',
                'line2' => null,
                'city' => 'Islamabad',
                'state_code' => null,
                'postal_code' => null,
                'country_code' => 'PK',
                'phone' => '+923001112222',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $usAddressId = DB::table('customer_addresses')
            ->where('user_id', $this->usUserId)
            ->where('label', 'Home')
            ->value('id');
        $pkAddressId = DB::table('customer_addresses')
            ->where('user_id', $this->pkUserId)
            ->where('label', 'Home')
            ->value('id');

        // Accounts
        DB::table('customer_accounts')->updateOrInsert(
            ['user_id' => $this->usUserId],
            [
                'marketing_email_opt_in' => true,
                'marketing_email_consented_at' => now()->subDays(20),
                'marketing_email_revoked_at' => null,
                'marketing_email_consent_ip' => '127.0.0.1',
                'marketing_email_consent_source' => 'seed',
                'marketing_sms_opt_in' => false,
                'marketing_sms_consented_at' => null,
                'marketing_sms_revoked_at' => now()->subDays(10),
                'marketing_sms_consent_ip' => '127.0.0.1',
                'marketing_sms_consent_source' => 'seed',
                'preferred_currency' => 'USD',
                'default_shipping_address_id' => $usAddressId,
                'default_billing_address_id' => $usAddressId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('customer_accounts')->updateOrInsert(
            ['user_id' => $this->pkUserId],
            [
                'marketing_email_opt_in' => false,
                'marketing_email_consented_at' => null,
                'marketing_email_revoked_at' => now()->subDays(15),
                'marketing_email_consent_ip' => '127.0.0.1',
                'marketing_email_consent_source' => 'seed',
                'marketing_sms_opt_in' => true,
                'marketing_sms_consented_at' => now()->subDays(7),
                'marketing_sms_revoked_at' => null,
                'marketing_sms_consent_ip' => '127.0.0.1',
                'marketing_sms_consent_source' => 'seed',
                'preferred_currency' => 'PKR',
                'default_shipping_address_id' => $pkAddressId,
                'default_billing_address_id' => $pkAddressId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Stats
        DB::table('customer_stats')->updateOrInsert(
            ['user_id' => $this->usUserId],
            [
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('customer_stats')->updateOrInsert(
            ['user_id' => $this->pkUserId],
            [
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
