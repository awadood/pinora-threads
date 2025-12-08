<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tax\TaxRuleResource;
use App\Models\TaxRule;
use App\Repositories\Tax\Contracts\ITaxRuleRepository;
use Illuminate\Http\Request;

/**
 * TaxRuleController
 *
 * Manage tax rules (priority, position, flags like calculate_subtotal
 * and applies_to_shipping).
 *
 * @author Abdul Wadood
 */
class TaxRuleController extends Controller
{
    public function __construct(protected ITaxRuleRepository $taxRuleRepository) {}

    protected function rules(Request $request, ?TaxRule $model = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'integer', 'min:0', 'max:65535'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'calculate_subtotal' => ['boolean'],
            'applies_to_shipping' => ['boolean'],
            'active' => ['boolean'],
        ];

        return $rules;
    }

    public function index()
    {
        $items = $this->taxRuleRepository->all();

        return TaxRuleResource::collection($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));

        $taxRule = $this->taxRuleRepository->create($validated);

        return TaxRuleResource::make($taxRule)->response()->setStatusCode(201);
    }

    public function show(TaxRule $taxRule)
    {
        return TaxRuleResource::make($taxRule);
    }

    public function update(Request $request, TaxRule $taxRule)
    {
        $validated = $request->validate($this->rules($request, $taxRule));

        $taxRule->fill($validated)->save();

        return TaxRuleResource::make($taxRule);
    }

    public function destroy(TaxRule $taxRule)
    {
        $this->taxRuleRepository->disableIfNotDestroy($taxRule);

        return response()->json([], 204);
    }
}
