<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Collection;
use App\Models\Product;
use App\Repositories\Catalog\Contracts\ICollectionProductRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * CollectionProductController
 *
 * Manage collection <-> product pivot table.
 *
 * @author Abdul Wadood
 */
class CollectionProductController extends Controller
{
    public function __construct(protected ICollectionProductRepository $pivots) {}

    public function attachProducts(Request $request, Collection $collection)
    {
        $data = $request->validate($this->rules(false));

        $changes = $this->pivots->attachProductsToCollection($collection, $data['product_ids']);

        return response()->json($changes);
    }

    public function syncProducts(Request $request, Collection $collection)
    {
        $data = $request->validate($this->rules(false, true));

        $changes = $this->pivots->syncProductsToCollection($collection, $data['product_ids']);

        return response()->json($changes);
    }

    public function detachProducts(Request $request, Collection $collection)
    {
        $data = $request->validate($this->rules(false));

        $changes = $this->pivots->detachProductsFromCollection($collection, $data['product_ids']);

        return response()->json($changes);
    }

    public function attachCollections(Request $request, Product $product)
    {
        $data = $request->validate($this->rules(true));

        $changes = $this->pivots->attachCollectionsToProduct($product, $data['collection_ids']);

        return response()->json($changes);
    }

    public function syncCollections(Request $request, Product $product)
    {
        $data = $request->validate($this->rules(true, true));

        $changes = $this->pivots->syncCollectionsToProduct($product, $data['collection_ids']);

        return response()->json($changes);
    }

    public function detachCollections(Request $request, Product $product)
    {
        $data = $request->validate($this->rules(true));

        $changes = $this->pivots->detachCollectionsFromProduct($product, $data['collection_ids']);

        return response()->json($changes);
    }

    private function rules(bool $forCollections, bool $allowEmptyArray = false)
    {
        $rules = [
            'items' => [$allowEmptyArray ? 'present' : 'required', 'array'],
            'items.*' => ['required', 'array'],
            'items.*.collection_id' => ['required', 'integer', 'exists:collections,id'],
            'items.*.sort' => ['required', 'integer', 'min:0', 'max:65535'],
        ];

        if ($forCollections) {
            $rules['items.*.collection_id'] = ['required', 'integer', 'exists:collections,id'];
        } else {
            $rules['items.*.product_id'] = ['required', 'integer', 'exists:products,id'];
        }

        return $rules;
    }
}
