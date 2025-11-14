<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Customer\CustomerGroupResource;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * CustomerGroupController
 *
 * @author Abdul Wadood
 */
class CustomerGroupController extends BaseLookupController
{
    protected string $modelClass = CustomerGroup::class;

    protected string $resourceClass = CustomerGroupResource::class;

    protected array $allowedFilters = ['code', 'name', 'active'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['sort_order', 'name', 'code'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255', Rule::unique('customer_groups', 'code')->ignore($model?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'active' => ['boolean'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('customer_groups', 'code')];
        }

        return $rules;
    }

    public function show(CustomerGroup $customerGroup)
    {
        return $this->performShow($customerGroup);
    }

    public function update(Request $request, CustomerGroup $customerGroup)
    {
        return $this->performUpdate($request, $customerGroup);
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        return $this->performDestroy($customerGroup);
    }
}
