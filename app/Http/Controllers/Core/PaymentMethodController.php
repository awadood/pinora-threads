<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * PaymentMethodController
 *
 * @author Abdul Wadood
 */
class PaymentMethodController extends BaseLookupController
{
    protected string $modelClass = PaymentMethod::class;

    protected string $resourceClass = PaymentMethodResource::class;

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
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('payment_methods', 'code')];
        }

        return $rules;
    }

    public function show(PaymentMethod $paymentMethod)
    {
        return $this->performShow($paymentMethod);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        return $this->performUpdate($request, $paymentMethod);
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        return $this->performDestroy($paymentMethod);
    }
}
