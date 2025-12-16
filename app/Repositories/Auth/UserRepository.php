<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Repositories\Auth\Contracts\IUserRepository;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * UserRepository
 *
 * Concrete repository for User model.
 *
 * @author Abdul Wadood
 */
class UserRepository extends BaseRepository implements IUserRepository
{
    protected string $modelClass = User::class;

    protected array $allowedSearchColumns = [
        'name' => true,
        'email' => true,
        'active' => true,
    ];

    public function syncRoles(User $user, array $roles): User
    {
        $user->syncRoles($roles);

        return $user->load('roles');
    }

    public function syncPermissions(User $user, array $permissions): User
    {
        $user->syncPermissions($permissions);

        return $user->load('permissions', 'roles');
    }

    public function setActive(User $user, bool $active): User
    {
        $user->update(['active' => $active]);

        return $user;
    }
}
