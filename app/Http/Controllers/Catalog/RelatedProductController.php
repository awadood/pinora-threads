<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\RelatedProductRequest;
use App\Http\Resources\Catalog\ProductResource;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Repositories\Catalog\Contracts\IRelatedProductRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * RelatedProductController
 *
 * Manage and expose manually curated related products.
 *
 * @author Abdul Wadood
 */
class RelatedProductController extends Controller
{
    use QueryFilterable;

    protected IRelatedProductRepository $related;

    protected IProductRepository $products;

    public function __construct(IRelatedProductRepository $related, IProductRepository $products)
    {
        $this->related = $related;
        $this->products = $products;

        $this->allowedFilters = [];
        $this->likeFilters = [];
        $this->allowedSorts = [];
    }

    public function indexByProductSlug(string $slug, Request $request)
    {
        $product = $this->products->query()->where('slug', $slug)->firstOrFail();

        $query = $this->products->query()
            ->select('products.*')
            ->join('related_products', 'related_products.related_product_id', '=', 'products.id')
            ->where('related_products.product_id', $product->id);

        $query = $this->applySorting($query, $request);

        return ProductResource::collection($query->get());
    }

    public function store(RelatedProductRequest $request)
    {
        $pivot = $this->related->create($request->validated());

        $product = $this->products->find($pivot->related_product_id);

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    public function destroy(int $product, int $related_product)
    {
        $record = $this->related->query()
            ->where('product_id', $product)
            ->where('related_product_id', $related_product)
            ->firstOrFail();

        $this->related->destroy($record->getKey());

        return response()->json([], 204);
    }
}
