<?php

namespace App\Repositories\Catalog;

use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\ICategoryProductRepository;
use Illuminate\Support\Facades\DB;

/**
 * CategoryProductRepository
 *
 * Concrete repository for CategoryProduct model.
 *
 * @author Abdul Wadood
 */
class CategoryProductRepository extends BaseRepository implements ICategoryProductRepository
{
    protected string $modelClass = CategoryProduct::class;

    public function attachProductsToCategory(Category $category, array $productIds): array
    {
        if (count($productIds) === 0) {
            return ['requested' => 0, 'inserted' => 0];
        }

        // Idempotent attach
        $changes = $category->products()->syncWithoutDetaching($productIds);

        return [
            'requested' => count($productIds),
            'inserted' => count($changes['attached']),
        ];
    }

    public function syncProductsToCategory(Category $category, array $productIds): array
    {
        return DB::transaction(function () use ($category, $productIds) {
            $changes = $category->products()->sync($productIds);

            return [
                'requested' => count($productIds),
                'deleted' => isset($changes['detached']) ? count($changes['detached']) : 0,
                'inserted' => isset($changes['attached']) ? count($changes['attached']) : 0,
            ];
        });
    }

    public function detachProductsFromCategory(Category $category, array $productIds): array
    {
        if (count($productIds) === 0) {
            return ['requested' => 0, 'deleted' => 0];
        }

        // detach() returns number of records deleted in most drivers
        $deleted = $category->products()->detach($productIds);

        return ['requested' => count($productIds), 'deleted' => (int) $deleted];
    }

    public function attachCategoriesToProduct(Product $product, array $categoryIds): array
    {
        if (count($categoryIds) === 0) {
            return ['requested' => 0, 'inserted' => 0];
        }

        // Idempotent attach
        $changes = $product->categories()->syncWithoutDetaching($categoryIds);

        return [
            'requested' => count($categoryIds),
            'inserted' => count($changes['attached']),
        ];
    }

    public function syncCategoriesToProduct(Product $product, array $categoryIds): array
    {
        return DB::transaction(function () use ($product, $categoryIds) {
            $chagnes = $product->categories()->sync($categoryIds);

            return [
                'requested' => count($categoryIds),
                'deleted' => isset($chagnes['detached']) ? count($chagnes['detached']) : 0,
                'inserted' => isset($chagnes['attached']) ? count($chagnes['attached']) : 0,
            ];
        });
    }

    public function detachCategoriesFromProduct(Product $product, array $categoryIds): array
    {
        if (count($categoryIds) === 0) {
            return ['requested' => 0, 'deleted' => 0];
        }

        // detach() returns number of records deleted in most drivers
        $deleted = $product->categories()->detach($categoryIds);

        return ['requested' => count($categoryIds), 'deleted' => (int) $deleted];
    }
}
