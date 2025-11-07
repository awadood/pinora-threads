<?php

namespace Database\Seeders;

use App\Models\User;
use App\Util\Permissions;
use App\Util\Roles;
use Illuminate\Database\Seeder;
use ReflectionClass;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. USE REFLECTION TO GET ALL PERMISSION CONSTANTS
        $reflection = new ReflectionClass(Permissions::class);

        $permissions = [];

        // 2. CREATE PERMISSIONS
        $this->command->info('Seeding Permissions...');
        foreach ($reflection->getConstants() as $key => $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
            $permissions[] = $permissionName;
        }
        $this->command->info('Permissions seeded successfully.');

        // 3. CREATE ROLES
        $this->command->info('Seeding Roles...');
        $rootUser = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN]);
        $user = User::where('email', 'admin@pinnorafashion.com')->first();
        $user->assignRole($rootUser);

        $adminRole = Role::firstOrCreate(['name' => Roles::ADMIN]);
        $adminRole->syncPermissions($permissions);

        // Standard User Role (Gets basic permissions)
        $managerRole = Role::firstOrCreate(['name' => Roles::INVENTORY_MANAGER]);
        $managerRole->givePermissionTo([
            Permissions::PRODUCT_CREATE,
        ]);

        $this->command->info('Roles and permissions synchronized.');
    }
}
