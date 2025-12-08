<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tax\TaxCalculationResource;
use App\Models\TaxCalculation;
use App\Repositories\Tax\Contracts\ITaxCalculationRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * TaxCalculationController
 *
 * Manage the tax_calculations matrix that links user tax class and
 * product tax class to specific tax rules and tax rates.
 *
 * @author Abdul Wadood
 */
class TaxCalculationController extends Controller
{
    public function __construct(protected ITaxCalculationRepository $taxCalculationRepository) {}

    protected function rules(Request $request, ?TaxCalculation $model = null): array
    {
        $uniqueRule = Rule::unique('tax_calculations', 'tax_rate_id')
            ->where(function ($query) use ($request, $model) {
                $query->where('tax_rule_id', $request->input('tax_rule_id'))
                    ->where('user_tax_class_id', $request->input('user_tax_class_id'))
                    ->where('product_tax_class_id', $request->input('product_tax_class_id'));

                if ($model !== null) {
                    $query->where('id', '!=', $model->getKey());
                }
            });

        return [
            'tax_rate_id' => ['required', 'integer', 'exists:tax_rates,id', $uniqueRule],
            'tax_rule_id' => ['required', 'integer', 'exists:tax_rules,id'],
            'user_tax_class_id' => ['required', 'integer', 'exists:tax_classes,id'],
            'product_tax_class_id' => ['required', 'integer', 'exists:tax_classes,id'],
        ];
    }

    public function index()
    {
        $items = $this->taxCalculationRepository->all();

        return TaxCalculationResource::collection($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));

        $taxCalculation = $this->taxCalculationRepository->create($validated);

        return TaxCalculationResource::make($taxCalculation)->response()->setStatusCode(201);
    }

    public function show(TaxCalculation $taxCalculation)
    {
        return TaxCalculationResource::make($taxCalculation);
    }

    public function update(Request $request, TaxCalculation $taxCalculation)
    {
        $validated = $request->validate($this->rules($request, $taxCalculation));

        $taxCalculation->fill($validated)->save();

        return TaxCalculationResource::make($taxCalculation);
    }

    public function destroy(TaxCalculation $taxCalculation)
    {
        $this->taxCalculationRepository->disableIfNotDestroy($taxCalculation);

        return response()->json([], 204);
    }
}
