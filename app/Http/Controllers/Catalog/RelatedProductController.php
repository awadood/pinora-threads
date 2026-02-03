<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\RelatedProductRequest;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use App\Repositories\Catalog\Contracts\IProductRepository;
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

    protected IProductRepository $products;

    public function __construct(IProductRepository $products)
    {
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
        $data = $request->validated();

        /** @var Product $product */
        $product = $this->products->query()->findOrFail($data['product_id']);
        $product->relatedProducts()->syncWithoutDetaching([(int) $data['related_product_id']]);

        $related = $this->products->query()->findOrFail($data['related_product_id']);

        return ProductResource::make($related)->response()->setStatusCode(201);
    }

    public function destroy(int $product, int $related_product)
    {
        /** @var Product $owner */
        $owner = $this->products->query()->findOrFail($product);
        $deleted = $owner->relatedProducts()->detach($related_product);
        if ((int) $deleted === 0) {
            abort(404);
        }

        return response()->json([], 204);
    }
}
