<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CoreTablesSeeder extends Seeder
{
    public function run(): void
    {
        $tables = config('lookups.tables', []);
        if (empty($tables)) {
            $this->command?->warn('No lookups configured.');

            return;
        }

        DB::transaction(function () use ($tables) {
            foreach ($tables as $table => $rows) {
                $this->seedTable($table, $rows);
            }
        });

        DB::table('countries')->upsert([
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'PK', 'name' => 'Pakistan'],
        ], ['code'], ['name']);

        DB::table('states')->upsert([
            ['code' => 'CA', 'name' => 'California', 'country_code' => 'US'],
            ['code' => 'NY', 'name' => 'New York',   'country_code' => 'US'],
        ], ['code'], ['name', 'country_code']);

        DB::table('currencies')->upsert([
            ['code' => 'USD', 'name' => 'US Dollar'],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee'],
        ], ['code'], ['name']);

        DB::table('customer_groups')->upsert([
            ['code' => 'standard', 'name' => 'Standard'],
            ['code' => 'b2b', 'name' => 'B2B'],
            ['code' => 'vip', 'name' => 'VIP'],
        ], ['code'], ['name']);

        if (Schema::hasTable('shipment_rates')) {
            DB::table('shipment_rates')->upsert([
                // PKR rates
                ['shipment_method_code' => 'pickup', 'currency_code' => 'PKR', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 10],
                ['shipment_method_code' => 'self', 'currency_code' => 'PKR', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 250, 'active' => true, 'sort_order' => 20],
                ['shipment_method_code' => 'courier', 'currency_code' => 'PKR', 'min_subtotal' => null, 'max_subtotal' => 14999, 'price' => 350, 'active' => true, 'sort_order' => 30],
                ['shipment_method_code' => 'courier', 'currency_code' => 'PKR', 'min_subtotal' => 15000, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 31],

                // USD rates
                ['shipment_method_code' => 'pickup', 'currency_code' => 'USD', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 10],
                ['shipment_method_code' => 'self', 'currency_code' => 'USD', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 5, 'active' => true, 'sort_order' => 20],
                ['shipment_method_code' => 'courier', 'currency_code' => 'USD', 'min_subtotal' => null, 'max_subtotal' => 99.99, 'price' => 8, 'active' => true, 'sort_order' => 30],
                ['shipment_method_code' => 'courier', 'currency_code' => 'USD', 'min_subtotal' => 100, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 31],
            ], ['shipment_method_code', 'currency_code', 'sort_order'], ['min_subtotal', 'max_subtotal', 'price', 'active']);
        }
    }

    private function seedTable(string $table, array $rows): void
    {
        foreach ($rows as $row) {
            // Normalize + defaults
            if (isset($row['code'])) {
                $row['code'] = strtolower(trim($row['code']));
            }

            $payload = array_merge([
                'name' => $row['name'] ?? $row['code'] ?? 'Unnamed',
                'active' => $row['active'] ?? true,
                'sort_order' => $row['sort_order'] ?? 0,
            ], $row);

            // Ensure we don’t try to update the match key itself in the update set
            $match = ['code' => $payload['code'] ?? $payload['name']];
            unset($payload['code']);

            DB::table($table)->updateOrInsert($match, $payload);
        }
    }
}
