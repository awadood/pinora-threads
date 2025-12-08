<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\RefundStatusResource;
use App\Models\RefundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * RefundStatusController
 *
 * @author Abdul Wadood
 */
class RefundStatusController extends BaseLookupController
{
    protected string $modelClass = RefundStatus::class;

    protected string $resourceClass = RefundStatusResource::class;

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
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('refund_statuses', 'code')];
        }

        return $rules;
    }

    public function show(RefundStatus $refundStatus)
    {
        return $this->performShow($refundStatus);
    }

    public function update(Request $request, RefundStatus $refundStatus)
    {
        return $this->performUpdate($request, $refundStatus);
    }

    public function destroy(RefundStatus $refundStatus)
    {
        return $this->performDestroy($refundStatus);
    }
}
