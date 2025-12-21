<?php

namespace App\Repositories\Catalog\Contracts;

use App\Models\Collection;
use App\Models\Product;
use App\Repositories\IBaseRepository;

/**
 * ICollectionProductRepository — repository contract for CollectionProduct model.
 *
 * Defines common CRUD and search operations via IBaseRepository.
 *
 * Semantics in v1:
 * - A product can belong to many collections.
 * - A collection can contain many products.
 * - Ordering inside a collection is controlled by collection_product.sort.
 *
 * @author Abdul Wadood
 */
interface ICollectionProductRepository extends IBaseRepository
{
    /**
     * Attach products to a collection (non-destructive).
     *
     * Semantics:
     * - Adds the given product IDs to the collection.
     * - Existing relations are ignored (idempotent).
     * - New relations get an assigned sort (append at end, deterministic).
     *
     * @param  array<int>  $productIds
     * @return array{requested:int, inserted:int}
     */
    public function attachProductsToCollection(Collection $collection, array $productIds): array;

    /**
     * Sync products for a collection (destructive replace).
     *
     * Semantics:
     * - After completion, the collection is related to exactly $productIds.
     * - Any relations not present in $productIds are removed.
     * - Missing relations are inserted.
     * - Sort order is deterministic and rewritten to 1..N matching $productIds order.
     * - The operation is atomic.
     *
     * @param  array<int>  $productIds  May be empty to clear all products from collection.
     * @return array{requested:int, deleted:int, inserted:int}
     */
    public function syncProductsToCollection(Collection $collection, array $productIds): array;

    /**
     * Detach products from a collection (non-destructive).
     *
     * Semantics:
     * - Removes only the specified relations (if they exist).
     * - Missing relations are ignored (idempotent).
     *
     * @param  array<int>  $productIds
     * @return array{requested:int, deleted:int}
     */
    public function detachProductsFromCollection(Collection $collection, array $productIds): array;

    /**
     * Attach collections to a product (non-destructive).
     *
     * Semantics:
     * - Adds the given collection IDs to the product.
     * - Existing relations are ignored (idempotent).
     * - New relations get an assigned sort at the end for each collection.
     *
     * @param  array<int>  $collectionIds
     * @return array{requested:int, inserted:int}
     */
    public function attachCollectionsToProduct(Product $product, array $collectionIds): array;

    /**
     * Sync collections for a product (destructive replace).
     *
     * Semantics:
     * - After completion, the product is related to exactly $collectionIds.
     * - Any relations not present in $collectionIds are removed.
     * - Missing relations are inserted.
     * - The operation is atomic.
     *
     * Note:
     * - This does not rewrite ordering of products inside collections globally.
     * - It only ensures membership exists/doesn't exist from the product perspective.
     *
     * @param  array<int>  $collectionIds  May be empty to clear all collections from product.
     * @return array{requested:int, deleted:int, inserted:int}
     */
    public function syncCollectionsToProduct(Product $product, array $collectionIds): array;

    /**
     * Detach collections from a product (non-destructive).
     *
     * Semantics:
     * - Removes only the specified relations (if they exist).
     * - Missing relations are ignored (idempotent).
     *
     * @param  array<int>  $collectionIds
     * @return array{requested:int, deleted:int}
     */
    public function detachCollectionsFromProduct(Product $product, array $collectionIds): array;
}
