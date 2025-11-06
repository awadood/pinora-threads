<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
