<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\PaymentStatusResource;
use App\Models\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * PaymentStatusController
 *
 * @author Abdul Wadood
 */
class PaymentStatusController extends BaseLookupController
{
    protected string $modelClass = PaymentStatus::class;

    protected string $resourceClass = PaymentStatusResource::class;

    protected array $allowedFilters = ['code', 'name', 'active'];

    protected array $likeFilters = ['name'];

    protected array $allowedSorts = ['sort_order', 'name', 'code'];

    protected function rules(Request $request, ?Model $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255', Rule::unique('payment_statuses', 'code')->ignore($model?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'active' => ['boolean'],
        ];

        if (Str::lower($request->method()) === 'post') {
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('payment_statuses', 'code')];
        }

        return $rules;
    }

    public function show(PaymentStatus $paymentStatus)
    {
        return $this->performShow($paymentStatus);
    }

    public function update(Request $request, PaymentStatus $paymentStatus)
    {
        return $this->performUpdate($request, $paymentStatus);
    }

    public function destroy(PaymentStatus $paymentStatus)
    {
        return $this->performDestroy($paymentStatus);
    }
}
