<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\CollectionRequest;
use App\Http\Resources\Catalog\CollectionResource;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Collection;
use App\Repositories\Catalog\Contracts\ICollectionRepository;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * CollectionController
 *
 * Manage curated collections and expose them to the storefront.
 *
 * @author Abdul Wadood
 */
class CollectionController extends Controller
{
    use QueryFilterable;

    public function __construct(protected ICollectionRepository $collections, protected IProductRepository $products)
    {
        $this->allowedFilters = ['name', 'slug', 'active'];
        $this->likeFilters = ['name', 'slug'];
        $this->allowedSorts = ['sort', 'name'];
    }

    public function index(Request $request)
    {
        $query = $this->applySorting(
            $this->applyFilters($this->collections->query(), $request),
            $request
        );

        return CollectionResource::collection($query->get());
    }

    public function showBySlug(string $slug)
    {
        $collection = $this->collections->query()->where('slug', $slug)->firstOrFail();

        return CollectionResource::make($collection);
    }

    public function indexByCollection(string $slug, Request $request)
    {
        $collection = $this->collections->query()->where('slug', $slug)->firstOrFail();

        $query = $this->products->query()
            ->select('products.*')
            ->join('collection_product', 'collection_product.product_id', '=', 'products.id')
            ->where('collection_product.collection_id', $collection->id);

        $query = $this->applySorting($query, $request);

        return ProductResource::collection($query->get());
    }

    public function store(CollectionRequest $request)
    {
        $collection = $this->collections->create($request->validated());

        return CollectionResource::make($collection)->response()->setStatusCode(201);
    }

    public function update(CollectionRequest $request, Collection $collection)
    {
        $collection->fill($request->validated())->save();

        return CollectionResource::make($collection);
    }

    public function destroy(Collection $collection)
    {
        $this->collections->disableIfNotDestroy($collection);

        return response()->json([], 204);
    }
}
