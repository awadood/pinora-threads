<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductIndexRequest;
use App\Http\Requests\Catalog\ProductRequest;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use App\Repositories\Catalog\Contracts\IProductRepository;
use App\Repositories\Catalog\Filters\ProductFilters;
use App\Support\ProductListQuery;
use App\Support\StockScopeResolver;
use App\Support\Storefront\StoreContext;
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
    public function __construct(
        protected IProductRepository $products,
        protected ProductFilters $filters,
        protected StockScopeResolver $stockScopeResolver,
    ) {}

    /**
     * GET /api/products
     *
     * Storefront profile:
     *  - returns product cards (one row per product) and ALWAYS includes selected_variant.
     * Admin profile:
     *  - returns products (one row per product) and omits selected_variant by default.
     */
    public function index(ProductIndexRequest $request)
    {
        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);

        $profile = $request->user()?->roles()->exists() ? ProductFilters::PROFILE_ADMIN : ProductFilters::PROFILE_STOREFRONT;

        $parsed = $this->filters->parse($request, $profile);

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 24);

        $stockIds = $this->stockScopeResolver->forCountry($ctx->country);

        $dto = new ProductListQuery(
            profile: $parsed['profile'],
            page: $page,
            perPage: $perPage,
            sort: $parsed['sort'],
            productFilters: $parsed['product'],
            variantFilters: $parsed['variant'],
            hasVariantConstraints: (bool) $parsed['has_variant_constraints'],
            countryCode: $ctx->country,
            currencyCode: $ctx->currency,
            stockIds: $stockIds,
            appliedEcho: $parsed['echo'],
        );

        $paginator = $this->products->lookup($dto);

        return ProductResource::collection($paginator)->additional([
            'query' => [
                'profile' => $dto->profile,
                'currency' => $dto->currencyCode,
                'sort' => $dto->sort,
                'applied' => $dto->appliedEcho,
            ],
        ]);
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
