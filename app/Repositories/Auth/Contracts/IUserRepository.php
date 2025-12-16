<?php

namespace App\Repositories\Auth\Contracts;

use App\Models\User;
use App\Repositories\IBaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * IUserRepository
 *
 * Repository contract for managing User models.
 *
 * Provides methods for user management, role/permission synchronization,
 * and status control.
 *
 * @author Abdul Wadood
 */
interface IUserRepository extends IBaseRepository
{
    /**
     * Syncs the roles associated with a user, detaching any roles not provided
     * and attaching new ones.
     *
     * @param  User  $user  The user model instance to sync roles for.
     * @param  array<int, string>  $roles  An array of role IDs or names to assign to the user.
     * @return User The updated user model instance.
     */
    public function syncRoles(User $user, array $roles): User;

    /**
     * Syncs the direct permissions associated with a user.
     *
     * @param  User  $user  The user model instance to sync permissions for.
     * @param  array<int, string>  $permissions  An array of permission IDs or names to assign to the user.
     * @return User The updated user model instance.
     */
    public function syncPermissions(User $user, array $permissions): User;

    /**
     * Sets the active status of a user.
     *
     * @param  User  $user  The user model instance to modify.
     * @param  bool  $active  The desired active status (true for active, false for inactive).
     * @return User The updated user model instance.
     */
    public function setActive(User $user, bool $active): User;
}
