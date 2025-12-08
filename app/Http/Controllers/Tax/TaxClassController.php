<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tax\TaxClassResource;
use App\Models\TaxClass;
use App\Repositories\Tax\Contracts\ITaxClassRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * TaxClassController
 *
 * Manage tax classes (e.g., Retail US, Wholesale US, Shipping US).
 *
 * @author Abdul Wadood
 */
class TaxClassController extends Controller
{
    public function __construct(protected ITaxClassRepository $taxClassRepository) {}

    protected function rules(Request $request, ?TaxClass $model = null): array
    {
        $uniqueRule = Rule::unique('tax_classes', 'name');

        if ($model !== null) {
            $uniqueRule = $uniqueRule->ignore($model->getKey());
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
        ];
    }

    public function index()
    {
        $items = $this->taxClassRepository->all();

        return TaxClassResource::collection($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));
        $taxClass = $this->taxClassRepository->create($validated);

        return TaxClassResource::make($taxClass)->response()->setStatusCode(201);
    }

    public function show(TaxClass $taxClass)
    {
        return TaxClassResource::make($taxClass);
    }

    public function update(Request $request, TaxClass $taxClass)
    {
        $validated = $request->validate($this->rules($request, $taxClass));

        $taxClass->fill($validated)->save();

        return TaxClassResource::make($taxClass);
    }

    public function destroy(TaxClass $taxClass)
    {
        $this->taxClassRepository->disableIfNotDestroy($taxClass);

        return response()->json([], 204);
    }
}
