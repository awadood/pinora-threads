<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Repositories\Auth\Contracts\IRoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private IRoleRepository $repository) {}

    public function index()
    {
        $roles = $this->repository->all();

        return RoleResource::collection($roles);
    }

    public function show(Role $role)
    {
        $role->load('permissions');

        return RoleResource::make($role);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $role = $this->repository->create(['name' => $data['name'], 'guard_name' => 'web']);

        if (! empty($data['permissions'])) {
            $role = $this->repository->syncPermissions($role, $data['permissions']);
        }

        RoleResource::make($role)->response()->setStatusCode(201);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate($this->rules());

        $role = $this->repository->update($role, $data);

        return RoleResource::make($role);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->repository->disableIfNotDestroy($role);

        return response()->json(['message' => __('auth.role.destroyed')]);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $data = $request->validate(['permissions' => ['required', 'array'], 'permissions.*' => ['string']]);

        $role = $this->repository->syncPermissions($role, $data['permissions']);

        return RoleResource::make($role);
    }

    private function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
        ];
    }
}
