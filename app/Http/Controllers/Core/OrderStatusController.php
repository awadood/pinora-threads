<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\OrderStatusResource;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * OrderStatusController
 *
 * @author Abdul Wadood
 */
class OrderStatusController extends BaseLookupController
{
    protected string $modelClass = OrderStatus::class;

    protected string $resourceClass = OrderStatusResource::class;

    protected array $allowedFilters = ['code', 'name', 'active'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['sort_order', 'name', 'code'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'active' => ['boolean'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('order_statuses', 'code')];
        }

        return $rules;
    }

    public function show(OrderStatus $orderStatus)
    {
        return $this->performShow($orderStatus);
    }

    public function update(Request $request, OrderStatus $orderStatus)
    {
        return $this->performUpdate($request, $orderStatus);
    }

    public function destroy(OrderStatus $orderStatus)
    {
        return $this->performDestroy($orderStatus);
    }
}
