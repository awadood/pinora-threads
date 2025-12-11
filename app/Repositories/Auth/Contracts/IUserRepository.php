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
     * Paginate the collection of users, with optional search and active status filters.
     *
     * @param  string|null  $search  The search string to filter users by (e.g., name or email). Default is null.
     * @param  bool|null  $active  Whether to filter users by their active status
     *                             (true for active, false for inactive). Default is null (no filtering).
     * @param  int  $perPage  The number of users to display per page. Default is 15.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator A paginated collection of User models.
     */
    public function paginate(?string $search = null, ?bool $active = null, int $perPage = 15): LengthAwarePaginator;

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
