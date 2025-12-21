<?php

namespace App\Repositories\Catalog\Contracts;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\IBaseRepository;

/**
 * ICategoryProductRepository — repository contract for CategoryProduct model.
 *
 * Defines common CRUD and search operations via IBaseRepository.
 *
 * @author Abdul Wadood
 */
interface ICategoryProductRepository extends IBaseRepository
{
    /**
     * Attach products to a category (non-destructive).
     *
     * Semantics:
     * - Adds the given product IDs to the category.
     * - Existing relations are ignored (idempotent).
     *
     * @param  Category  $categoryId
     * @param  array<int>  $productIds
     * @return array{requested:int, inserted:int}
     */
    public function attachProductsToCategory(Category $category, array $productIds): array;

    /**
     * Sync products for a category (destructive replace).
     *
     * Semantics:
     * - After completion, the category is related to exactly $productIds.
     * - Any relations not present in $productIds are removed.
     * - Missing relations are inserted.
     * - The operation is atomic.
     *
     * @param  Category  $categoryId
     * @param  array<int>  $productIds  May be empty to clear all products from category.
     * @return array{requested:int, deleted:int, inserted:int}
     */
    public function syncProductsToCategory(Category $category, array $productIds): array;

    /**
     * Detach products from a category (non-destructive).
     *
     * Semantics:
     * - Removes only the specified relations (if they exist).
     * - Missing relations are ignored (idempotent).
     *
     * @param  Category  $categoryId
     * @param  array<int>  $productIds
     * @return array{requested:int, deleted:int}
     */
    public function detachProductsFromCategory(Category $category, array $productIds): array;

    /**
     * Attach categories to a product (non-destructive).
     *
     * Semantics:
     * - Adds the given category IDs to the product.
     * - Existing relations are ignored (idempotent).
     *
     * @param  Product  $productId
     * @param  array<int>  $categoryIds
     * @return array{requested:int, inserted:int}
     */
    public function attachCategoriesToProduct(Product $product, array $categoryIds): array;

    /**
     * Sync categories for a product (destructive replace).
     *
     * Semantics:
     * - After completion, the product is related to exactly $categoryIds.
     * - Any relations not present in $categoryIds are removed.
     * - Missing relations are inserted.
     * - The operation is atomic.
     *
     * @param  Product  $productId
     * @param  array<int>  $categoryIds  May be empty to clear all categories from product.
     * @return array{requested:int, deleted:int, inserted:int}
     */
    public function syncCategoriesToProduct(Product $product, array $categoryIds): array;

    /**
     * Detach categories from a product (non-destructive).
     *
     * Semantics:
     * - Removes only the specified relations (if they exist).
     * - Missing relations are ignored (idempotent).
     *
     * @param  Product  $productId
     * @param  array<int>  $categoryIds
     * @return array{requested:int, deleted:int}
     */
    public function detachCategoriesFromProduct(Product $product, array $categoryIds): array;
}
