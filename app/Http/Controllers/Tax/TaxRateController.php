<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tax\TaxRateResource;
use App\Models\TaxRate;
use App\Repositories\Tax\Contracts\ITaxRateRepository;
use Illuminate\Http\Request;

/**
 * TaxRateController
 *
 * Manage tax rates & geography (country/state/ZIP).
 *
 * @author Abdul Wadood
 */
class TaxRateController extends Controller
{
    public function __construct(protected ITaxRateRepository $taxRateRepository) {}

    protected function rules(Request $request, ?TaxRate $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'percentage' => ['required', 'boolean'],
            'refundable' => ['boolean'],
            'country_code' => ['required', 'string', 'size:2', 'exists:countries,code'],
            'state_code' => ['nullable', 'string', 'max:10', 'exists:states,code'],
            'zipcode' => ['required', 'string', 'max:20'],
            'zip_is_range' => ['nullable', 'boolean'],
            'zip_from' => ['nullable', 'string', 'max:20'],
            'zip_to' => ['nullable', 'string', 'max:20'],
            'active' => ['boolean'],
        ];

        return $rules;
    }

    public function index()
    {
        $items = $this->taxRateRepository->all();

        return TaxRateResource::collection($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));

        $taxRate = $this->taxRateRepository->create($validated);

        return (TaxRateResource::make($taxRate))->response()->setStatusCode(201);
    }

    public function show(TaxRate $taxRate)
    {
        return TaxRateResource::make($taxRate);
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate($this->rules($request, $taxRate));

        $taxRate->fill($validated)->save();

        return TaxRateResource::make($taxRate);
    }

    public function destroy(TaxRate $taxRate)
    {
        $this->taxRateRepository->disableIfNotDestroy($taxRate);

        return response()->json([], 204);
    }
}
