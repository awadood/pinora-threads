<?php

namespace App\Repositories\Catalog;

use App\Models\Collection;
use App\Models\CollectionProduct;
use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\ICollectionProductRepository;
use Illuminate\Support\Facades\DB;

/**
 * CollectionProductRepository
 *
 * Concrete repository for CollectionProduct model.
 *
 * Uses the same Eloquent relationship + sync/syncWithoutDetaching/detach pattern
 * as CategoryProductRepository, with the only extension being sort handling.
 *
 * @author Abdul Wadood
 */
class CollectionProductRepository extends BaseRepository implements ICollectionProductRepository
{
    protected string $modelClass = CollectionProduct::class;

    public function attachProductsToCollection(Collection $collection, array $productIds): array
    {
        if (count($productIds) === 0) {
            return ['requested' => 0, 'inserted' => 0];
        }

        $changes = $collection->products()->syncWithoutDetaching($productIds);

        return [
            'requested' => count($productIds),
            'inserted' => count($changes['attached']),
        ];
    }

    public function syncProductsToCollection(Collection $collection, array $productIds): array
    {
        return DB::transaction(function () use ($collection, $productIds) {
            $changes = $collection->products()->sync($productIds);

            return [
                'requested' => count($productIds),
                'deleted' => isset($changes['detached']) ? count($changes['detached']) : 0,
                'inserted' => isset($changes['attached']) ? count($changes['attached']) : 0,
            ];
        });
    }

    public function detachProductsFromCollection(Collection $collection, array $productIds): array
    {
        if (count($productIds) === 0) {
            return ['requested' => 0, 'deleted' => 0];
        }

        // detach() returns number of records deleted in most drivers
        $deleted = $collection->products()->detach($productIds);

        return ['requested' => count($productIds), 'deleted' => (int) $deleted];
    }

    public function attachCollectionsToProduct(Product $product, array $collectionIds): array
    {
        if (count($collectionIds) === 0) {
            return ['requested' => 0, 'inserted' => 0];
        }

        // Idempotent attach
        $changes = $product->collections()->syncWithoutDetaching($collectionIds);

        return [
            'requested' => count($collectionIds),
            'inserted' => count($changes['attached']),
        ];
    }

    public function syncCollectionsToProduct(Product $product, array $collectionIds): array
    {
        return DB::transaction(function () use ($product, $collectionIds) {
            $chagnes = $product->collections()->sync($collectionIds);

            return [
                'requested' => count($collectionIds),
                'deleted' => isset($chagnes['detached']) ? count($chagnes['detached']) : 0,
                'inserted' => isset($chagnes['attached']) ? count($chagnes['attached']) : 0,
            ];
        });
    }

    public function detachCollectionsFromProduct(Product $product, array $collectionIds): array
    {
        if (count($collectionIds) === 0) {
            return ['requested' => 0, 'deleted' => 0];
        }

        // detach() returns number of records deleted in most drivers
        $deleted = $product->collections()->detach($collectionIds);

        return ['requested' => count($collectionIds), 'deleted' => (int) $deleted];
    }
}
