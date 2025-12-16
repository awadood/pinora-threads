<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Auth\Contracts\IUserRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use QueryFilterable;

    public function __construct(protected IUserRepository $users) {}

    public function index(Request $request)
    {
        $query = $this->applySorting(
            $this->applyFilters($this->users->query(), $request),
            $request
        );

        return UserResource::collection(
            $query->whereHas('roles')->paginate($request->integer('per_page', 25))
        );
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make('password');
        $data['active'] = $data['active'] ?? true;
        $data['email_verified_at'] = Carbon::now();
        $data['phone_verified_at'] = Carbon::now();

        $user = $this->users->create($data);

        if (! empty($data['roles'])) {
            $user = $this->users->syncRoles($user, $data['roles']);
        }

        return UserResource::make($user);
    }

    public function show(User $user)
    {
        $user->load('roles', 'permissions');

        return UserResource::make($user);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        $user->update(Arr::except($data, 'roles'));

        if (Arr::exists($data, 'roles')) {
            $user = $this->users->syncRoles($user, $data['roles']);
        }

        return UserResource::make($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->users->disableIfNotDestroy($user);

        return response()->json(['message' => __('auth.destroyed')]);
    }

    public function syncRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user = $this->users->syncRoles($user, $data['roles']);

        return UserResource::make($user);
    }

    public function syncPermissions(Request $request, User $user)
    {
        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user = $this->users->syncPermissions($user, $data['permissions']);

        return UserResource::make($user);
    }

    public function toggleStatus(Request $request, User $user)
    {
        $user = $this->users->setActive($user, (bool) $request->input('active'));

        return UserResource::make($user);
    }
}
