<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\CountryResource;
use App\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * CountryController
 *
 * @author Abdul Wadood
 */
class CountryController extends BaseLookupController
{
    protected string $modelClass = Country::class;

    protected string $resourceClass = CountryResource::class;

    protected array $allowedFilters = ['code', 'name'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['code', 'name'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'size:2', 'alpha', Rule::unique('countries', 'code')->ignore($model?->getKey())],
            'name' => ['required', 'string'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'size:2', 'alpha', Rule::unique('countries', 'code')];
        }

        return $rules;
    }

    public function show(Country $country)
    {
        return $this->performShow($country);
    }

    public function update(Request $request, Country $country)
    {
        return $this->performUpdate($request, $country);
    }

    public function destroy(Country $country)
    {
        return $this->performDestroy($country);
    }
}
