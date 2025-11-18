<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductRequest;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
        $product = $this->products->query()->where('slug', $slug)->firstOrFail();

        return ProductResource::make($product);
    }

    public function store(ProductRequest $request)
    {
        $product = $this->products->create($request->validated());

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->fill($request->validated())->save();

        return ProductResource::make($product);
    }

    public function destroy(Product $product)
    {
        $this->products->disableIfNotDestroy($product);

        return response()->json([], 204);
    }
}
