<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('tax_classes')->upsert([
                ['id' => 1, 'name' => 'Standard', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Reduced',  'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Exempt',   'created_at' => now(), 'updated_at' => now()],
            ], ['id'], ['name', 'updated_at']);

            $taxRuleId = DB::table('tax_rules')->insertGetId([
                'code' => 'US_BASE',
                'priority' => 1,
                'position' => 1,
                'calculate_subtotal' => false,
                'applies_to_shipping' => false,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $taxRateId = DB::table('tax_rates')->insertGetId([
                'code' => 'US-CA-*-R1',
                'amount' => 8.25,
                'percentage' => true,
                'refundable' => true,
                'country_code' => 'US',
                'state_code' => 'CA',
                'zipcode' => '*',
                'zip_is_range' => false,
                'zip_from' => null,
                'zip_to' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // This table *does* have a unique composite per pfcore, so upsert is fine
            DB::table('tax_calculations')->upsert([
                [
                    'tax_rate_id' => $taxRateId,
                    'tax_rule_id' => $taxRuleId,
                    'user_tax_class_id' => 1,
                    'product_tax_class_id' => 1,
                ],
            ], ['tax_rate_id', 'tax_rule_id', 'user_tax_class_id', 'product_tax_class_id'], []);
        });
    }
}
