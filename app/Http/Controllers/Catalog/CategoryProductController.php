<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * CategoryProductController
 *
 * Manage category <-> product pivot table.
 *
 * @author Abdul Wadood
 */
class CategoryProductController extends Controller
{
    public function attachProducts(Request $request, Category $category)
    {
        $data = $request->validate($this->rulesProduct());

        $productIds = $data['product_ids'];
        if (count($productIds) === 0) {
            return response()->json(['requested' => 0, 'inserted' => 0]);
        }

        $changes = $category->products()->syncWithoutDetaching($productIds);

        return response()->json([
            'requested' => count($productIds),
            'inserted' => count($changes['attached']),
        ]);
    }

    public function syncProducts(Request $request, Category $category)
    {
        $data = $request->validate($this->rulesProduct(true));

        $productIds = $data['product_ids'];
        $changes = DB::transaction(function () use ($category, $productIds) {
            return $category->products()->sync($productIds);
        });

        return response()->json([
            'requested' => count($productIds),
            'deleted' => isset($changes['detached']) ? count($changes['detached']) : 0,
            'inserted' => isset($changes['attached']) ? count($changes['attached']) : 0,
        ]);
    }

    public function detachProducts(Request $request, Category $category)
    {
        $data = $request->validate($this->rulesProduct());

        $productIds = $data['product_ids'];
        if (count($productIds) === 0) {
            return response()->json(['requested' => 0, 'deleted' => 0]);
        }

        $deleted = $category->products()->detach($productIds);

        return response()->json([
            'requested' => count($productIds),
            'deleted' => (int) $deleted,
        ]);
    }

    public function attachCategories(Request $request, Product $product)
    {
        $data = $request->validate($this->rulesCateogry());

        $categoryIds = $data['category_ids'];
        if (count($categoryIds) === 0) {
            return response()->json(['requested' => 0, 'inserted' => 0]);
        }

        $changes = $product->categories()->syncWithoutDetaching($categoryIds);

        return response()->json([
            'requested' => count($categoryIds),
            'inserted' => count($changes['attached']),
        ]);
    }

    public function syncCategories(Request $request, Product $product)
    {
        $data = $request->validate($this->rulesCateogry(true));

        $categoryIds = $data['category_ids'];
        $changes = DB::transaction(function () use ($product, $categoryIds) {
            return $product->categories()->sync($categoryIds);
        });

        return response()->json([
            'requested' => count($categoryIds),
            'deleted' => isset($changes['detached']) ? count($changes['detached']) : 0,
            'inserted' => isset($changes['attached']) ? count($changes['attached']) : 0,
        ]);
    }

    public function detachCategories(Request $request, Product $product)
    {
        $data = $request->validate($this->rulesCateogry());

        $categoryIds = $data['category_ids'];
        if (count($categoryIds) === 0) {
            return response()->json(['requested' => 0, 'deleted' => 0]);
        }

        $deleted = $product->categories()->detach($categoryIds);

        return response()->json([
            'requested' => count($categoryIds),
            'deleted' => (int) $deleted,
        ]);
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
