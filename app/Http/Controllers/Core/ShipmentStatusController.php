<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\ShipmentStatusResource;
use App\Models\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * ShipmentStatusController
 *
 * @author Abdul Wadood
 */
class ShipmentStatusController extends BaseLookupController
{
    protected string $modelClass = ShipmentStatus::class;

    protected string $resourceClass = ShipmentStatusResource::class;

    protected array $allowedFilters = ['code', 'name', 'active'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['sort_order', 'name', 'code'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255', Rule::unique('shipment_statuses', 'code')->ignore($model?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'active' => ['boolean'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('shipment_statuses', 'code')];
        }

        return $rules;
    }

    public function show(ShipmentStatus $shipmentStatus)
    {
        return $this->performShow($shipmentStatus);
    }

    public function update(Request $request, ShipmentStatus $shipmentStatus)
    {
        return $this->performUpdate($request, $shipmentStatus);
    }

    public function destroy(ShipmentStatus $shipmentStatus)
    {
        return $this->performDestroy($shipmentStatus);
    }
}
