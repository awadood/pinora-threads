<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\StockMovementTypeResource;
use App\Models\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * StockMovementTypeController
 *
 * @author Abdul Wadood
 */
class StockMovementTypeController extends BaseLookupController
{
    protected string $modelClass = StockMovementType::class;

    protected string $resourceClass = StockMovementTypeResource::class;

    protected array $allowedFilters = ['code', 'name', 'active'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['sort_order', 'name', 'code'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255', Rule::unique('stock_movement_types', 'code')->ignore($model?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'active' => ['boolean'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('stock_movement_types', 'code')];
        }

        return $rules;
    }

    public function show(StockMovementType $stockMovementType)
    {
        return $this->performShow($stockMovementType);
    }

    public function update(Request $request, StockMovementType $stockMovementType)
    {
        return $this->performUpdate($request, $stockMovementType);
    }

    public function destroy(StockMovementType $stockMovementType)
    {
        return $this->performDestroy($stockMovementType);
    }
}
