<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Catalog\Contracts\ICategoryProductRepository;
use Illuminate\Http\Request;
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
    public function __construct(protected ICategoryProductRepository $pivots) {}

    public function attachProducts(Request $request, Category $category)
    {
        $data = $request->validate($this->rulesProduct());

        $changes = $this->pivots->attachProductsToCategory($category, $data['product_ids']);

        return response()->json($changes);
    }

    public function syncProducts(Request $request, Category $category)
    {
        $data = $request->validate($this->rulesProduct(true));

        $changes = $this->pivots->syncProductsToCategory($category, $data['product_ids']);

        return response()->json($changes);
    }

    public function detachProducts(Request $request, Category $category)
    {
        $data = $request->validate($this->rulesProduct());

        $changes = $this->pivots->detachProductsFromCategory($category, $data['product_ids']);

        return response()->json($changes);
    }

    public function attachCategories(Request $request, Product $product)
    {
        $data = $request->validate($this->rulesCateogry());

        $changes = $this->pivots->attachCategoriesToProduct($product, $data['category_ids']);

        return response()->json($changes);
    }

    public function syncCategories(Request $request, Product $product)
    {
        $data = $request->validate($this->rulesCateogry(true));

        $changes = $this->pivots->syncCategoriesToProduct($product, $data['category_ids']);

        return response()->json($changes);
    }

    public function detachCategories(Request $request, Product $product)
    {
        $data = $request->validate($this->rulesCateogry());

        $changes = $this->pivots->detachCategoriesFromProduct($product, $data['category_ids']);

        return response()->json($changes);
    }

    private function rulesCateogry(bool $allowEmptyArray = false)
    {
        return [
            'category_ids' => [$allowEmptyArray ? 'present' : 'required', 'array', 'max:50'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    private function rulesProduct(bool $allowEmptyArray = false)
    {
        return [
            'product_ids' => [$allowEmptyArray ? 'present' : 'required', 'array', 'max:50'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }
}
