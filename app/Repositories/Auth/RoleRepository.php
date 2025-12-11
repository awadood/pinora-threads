<?php

namespace App\Repositories\Auth;

use App\Repositories\Auth\Contracts\IRoleRepository;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;

/**
 * UserRepository
 *
 * Concrete repository for User model.
 *
 * @author Abdul Wadood
 */
class RoleRepository extends BaseRepository implements IRoleRepository
{
    protected string $modelClass = Role::class;

    public function update(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (Arr::exists($data, 'permissions')) {
            $role = $this->syncPermissions($role, $data['permissions']);
        }

        return $role;
    }

    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }
}
