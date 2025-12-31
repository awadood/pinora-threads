<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductRequest;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

/**
 * ProductController
 *
 * Manage products and expose listings / PDPs.
 *
 * @author Abdul Wadood
 */
class ProductController extends Controller
{
    use QueryFilterable;

    public function __construct(protected IProductRepository $products)
    {
        $this->allowedFilters = ['sku', 'name', 'slug', 'type', 'active', 'tax_class_id'];
        $this->likeFilters = ['sku', 'name', 'slug'];
        $this->allowedSorts = ['name', 'sku', 'id'];
    }

    public function index(Request $request)
    {
        $query = $this->applySorting(
            $this->applyFilters($this->products->query(), $request),
            $request
        );

        return ProductResource::collection($query->get());
    }

    public function showBySlug(string $slug)
    {
        $with = ['categories', 'prices', 'bundles', 'media.asset.renditions', 'variants.prices', 'variants.media.asset.renditions'];

        $product = $this->products->query()->with($with)->where('slug', $slug)->firstOrFail();

        return ProductResource::make($product);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['active'] = false;

        $product = $this->products->createWithDefaultVariant($request->validated());

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        if (Arr::exists($data, 'active')) {
            Arr::forget($data, 'active');
        }

        $product->update($data);

        return ProductResource::make($product);
    }

    public function destroy(Product $product)
    {
        $this->products->disableIfNotDestroy($product);

        return response()->json([], 204);
    }

    public function activate(Product $product)
    {
        $this->products->activate($product);

        return response()->json([], 204);
    }

    public function deactivate(Product $product)
    {
        $this->products->deactivate($product);

        return response()->json([], 204);
    }
}
