<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = 'admin@pinorathreads.com';
        User::firstOrCreate(['email' => $email], ['name' => 'Administrator', 'password' => Hash::make('password')]);

        $this->call([
            CoreTablesSeeder::class,
            TaxSeeder::class,
            PermissionSeeder::class,
            CatalogSeeder::class,
            InventorySeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
