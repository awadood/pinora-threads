<?php

namespace App\Repositories\Auth\Contracts;

use App\Repositories\IBaseRepository;
use Spatie\Permission\Models\Role;

/**
 * IRoleRepository
 *
 * Repository contract for managing role models.
 *
 * Provides methods for role management, role/permission synchronization.
 *
 * @author Abdul Wadood
 */
interface IRoleRepository extends IBaseRepository
{
    public function update(Role $role, array $data): Role;

    /**
     * Synchronizes the permissions associated with a specific role.
     *
     * This method detaches any permissions currently assigned to the role
     * that are not present in the provided array, and attaches any new permissions.
     *
     * @param  \App\Models\Role  $role  The role model instance to synchronize permissions for.
     * @param  array<int, string>  $permissions  An array of permission IDs or names to assign to the role.
     * @return \App\Models\Role The updated role model instance.
     */
    public function syncPermissions(Role $role, array $permissions): Role;
}
