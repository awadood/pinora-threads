<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\StateResource;
use App\Models\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * StateController
 *
 * @author Abdul Wadood
 */
class StateController extends BaseLookupController
{
    protected string $modelClass = State::class;

    protected string $resourceClass = StateResource::class;

    protected array $allowedFilters = ['code', 'name', 'country_code'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['code', 'name'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'size:2', 'alpha', Rule::unique('states', 'code')->ignore($model?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2', 'alpha', 'exists:countries,code'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'size:2', 'alpha', Rule::unique('states', 'code')];
        }

        return $rules;
    }

    public function show(State $state)
    {
        return $this->performShow($state);
    }

    public function update(Request $request, State $state)
    {
        return $this->performUpdate($request, $state);
    }

    public function destroy(State $state)
    {
        return $this->performDestroy($state);
    }
}
