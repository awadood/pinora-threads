<?php

namespace App\Http\Controllers\Core;

use App\Http\Resources\Core\InvoiceStatusResource;
use App\Models\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * InvoiceStatusController
 *
 * @author Abdul Wadood
 */
class InvoiceStatusController extends BaseLookupController
{
    protected string $modelClass = InvoiceStatus::class;

    protected string $resourceClass = InvoiceStatusResource::class;

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
            $rules['code'] = ['required', 'string', 'max:255', Rule::unique('invoice_statuses', 'code')];
        }

        return $rules;
    }

    public function show(InvoiceStatus $invoiceStatus)
    {
        return $this->performShow($invoiceStatus);
    }

    public function update(Request $request, InvoiceStatus $invoiceStatus)
    {
        return $this->performUpdate($request, $invoiceStatus);
    }

    public function destroy(InvoiceStatus $invoiceStatus)
    {
        return $this->performDestroy($invoiceStatus);
    }
}
