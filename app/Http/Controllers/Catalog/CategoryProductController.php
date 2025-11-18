<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\CategoryProductRequest;
use App\Repositories\Catalog\Contracts\ICategoryProductRepository;
use Illuminate\Routing\Controller;

/**
 * CategoryProductController
 *
 * Manage category <-> product pivot table.
 *
 * @author Abdul Wadood
 */
class CategoryProductController extends Controller
{
    public function __construct(protected ICategoryProductRepository $pivots){}

    public function store(CategoryProductRequest $request)
    {
        $pivot = $this->pivots->create($request->validated());

        return response()->json($pivot, 201);
    }

    public function destroy(int $category, int $product)
    {
        $record = $this->pivots->query()
            ->where('category_id', $category)
            ->where('product_id', $product)
            ->firstOrFail();

        $this->pivots->destroy($record->getKey());

        return response()->json([], 204);
    }
}
