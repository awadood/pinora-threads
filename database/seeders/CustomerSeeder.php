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
            $this->seedProfilesAndAddresses();
        });
    }

    protected function seedUsersAndGroups(): void
    {
        $usUser = User::firstOrCreate(
            ['email' => 'us.customer@example.com'],
            [
                'name' => 'US Customer',
                'password' => Hash::make('password'),
            ]
        );
        $pkUser = User::firstOrCreate(
            ['email' => 'pk.customer@example.com'],
            [
                'name' => 'PK Customer',
                'password' => Hash::make('password'),
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

    protected function seedProfilesAndAddresses(): void
    {
        // Profiles
        DB::table('customer_profiles')->updateOrInsert(
            ['user_id' => $this->usUserId],
            [
                'tax_class_id' => 1,
                'marketing_email_opt_in' => true,
                'marketing_sms_opt_in' => false,
                'preferred_currency' => 'USD',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('customer_profiles')->updateOrInsert(
            ['user_id' => $this->pkUserId],
            [
                'tax_class_id' => 1,
                'marketing_email_opt_in' => false,
                'marketing_sms_opt_in' => true,
                'preferred_currency' => 'PKR',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Addresses
        DB::table('addresses')->updateOrInsert(
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
                'default_shipping' => true,
                'default_billing' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('addresses')->updateOrInsert(
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
                'default_shipping' => true,
                'default_billing' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
