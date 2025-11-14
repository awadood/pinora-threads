<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\ShipmentMethodResource;
use App\Models\ShipmentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * ShipmentMethodController
 *
 * @author Abdul Wadood
 */
class ShipmentMethodController extends BaseLookupController
{
    protected string $modelClass = ShipmentMethod::class;

    protected string $resourceClass = ShipmentMethodResource::class;

    protected array $allowedFilters = ['code', 'name', 'active'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['sort_order', 'name', 'code'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255', Rule::unique('shipment_methods', 'code')->ignore($model?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'active' => ['boolean'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('shipment_methods', 'code')];
        }

        return $rules;
    }

    public function show(ShipmentMethod $shipmentMethod)
    {
        return $this->performShow($shipmentMethod);
    }

    public function update(Request $request, ShipmentMethod $shipmentMethod)
    {
        return $this->performUpdate($request, $shipmentMethod);
    }

    public function destroy(ShipmentMethod $shipmentMethod)
    {
        return $this->performDestroy($shipmentMethod);
    }
}
