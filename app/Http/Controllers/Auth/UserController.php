<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Auth\Contracts\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(protected IUserRepository $repository) {}

    public function index(Request $request)
    {
        $users = $this->repository->paginate($request->input('search'), $request->input('active'), $request->input('per_page', 15));

        return UserResource::collection($users);
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make('password');
        $data['active'] = $data['active'] ?? true;
        $data['email_verified_at'] = Carbon::now();
        $data['phone_verified_at'] = Carbon::now();

        $user = $this->repository->create($data);

        if (! empty($data['roles'])) {
            $user = $this->repository->syncRoles($user, $data['roles']);
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
            $user = $this->repository->syncRoles($user, $data['roles']);
        }

        return UserResource::make($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->repository->disableIfNotDestroy($user);

        return response()->json(['message' => __('auth.destroyed')]);
    }

    public function syncRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user = $this->repository->syncRoles($user, $data['roles']);

        return UserResource::make($user);
    }

    public function syncPermissions(Request $request, User $user)
    {
        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user = $this->repository->syncPermissions($user, $data['permissions']);

        return UserResource::make($user);
    }

    public function toggleStatus(Request $request, User $user)
    {
        $user = $this->repository->setActive($user, (bool) $request->input('active'));

        return UserResource::make($user);
    }
}
