<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductVariantRequest;
use App\Http\Resources\Catalog\VariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Catalog\Contracts\IProductVariantRepository;
use App\Support\QueryFilterable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * VariantController
 *
 * Manage product variants and expose variant matrices.
 *
 * @author Abdul Wadood
 */
class VariantController extends Controller
{
    use QueryFilterable;

    public function __construct(protected IProductVariantRepository $variants)
    {
        $this->allowedFilters = ['product_id', 'sku', 'title', 'active', 'default'];
        $this->likeFilters = ['sku', 'title'];
        $this->allowedSorts = ['id'];
    }

    public function index(Request $request)
    {
        $items = $this->variants->lookup(
            $request->query('filter', []),
            ['product', 'attributes.option.attribute']
        );

        return VariantResource::collection($items);
    }

    public function indexByProductSlug(string $slug, Request $request)
    {
        $query = $this->variants->query()
            ->select('product_variants.*')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('products.slug', $slug);

        $query = $this->applySorting($this->applyFilters($query, $request), $request);

        return VariantResource::collection($query->get());
    }

    public function show(ProductVariant $id)
    {
        return VariantResource::make($id);
    }

    public function store(ProductVariantRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['product_id'] = $product->id;

        $variant = $this->variants->create($data);

        return VariantResource::make($variant)->response()->setStatusCode(201);
    }

    public function update(ProductVariantRequest $request, ProductVariant $variant)
    {
        $variant->fill($request->validated())->save();

        return VariantResource::make($variant);
    }

    public function destroy(ProductVariant $variant)
    {
        $this->variants->disableIfNotDestroy($variant);

        return response()->json([], 204);
    }
}
