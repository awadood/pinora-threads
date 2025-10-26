<?php

namespace App\Repository;

use App\Models\User;
use App\Repository\Contracts\IUserRepository;

/**
 * Class UserRepository
 *
 * @author Abdul Wadood
 */
class UserRepository extends BaseRepository implements IUserRepository
{
    protected string $model = User::class;
}
