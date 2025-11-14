<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\CurrencyResource;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * CurrencyController
 *
 * @author Abdul Wadood
 */
class CurrencyController extends BaseLookupController
{
    protected string $modelClass = Currency::class;

    protected string $resourceClass = CurrencyResource::class;

    protected array $allowedFilters = ['code', 'name'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['code', 'name'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = ['name' => ['required', 'string']];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'size:3', 'alpha', Rule::unique('currencies', 'code')];
        }

        return $rules;
    }

    public function show(Currency $currency)
    {
        return $this->performShow($currency);
    }

    public function update(Request $request, Currency $currency)
    {
        return $this->performUpdate($request, $currency);
    }

    public function destroy(Currency $currency)
    {
        return $this->performDestroy($currency);
    }
}
