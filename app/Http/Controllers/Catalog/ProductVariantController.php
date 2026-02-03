<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductVariantLinkRequest;
use App\Http\Resources\Catalog\ProductResource;
use App\Models\Product;
use App\Support\QueryFilterable;
use App\Support\StockScopeResolver;
use App\Support\Storefront\StoreContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * ProductVariantController
 *
 * Manage product-to-product variant links and expose variant lists.
 *
 * @author Abdul Wadood
 */
class ProductVariantController extends Controller
{
    use QueryFilterable;

    public function __construct()
    {
        $this->allowedFilters = ['id', 'sku', 'name', 'slug', 'active', 'type'];
        $this->likeFilters = ['sku', 'name', 'slug'];
        $this->allowedSorts = ['id', 'name'];
    }

    /**
     * GET /api/products/lookup
     *
     * Lookup products for admin selectors/typeahead.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        $query = $this->applySorting($this->applyFilters($query, $request), $request);

        return ProductResource::collection($query->limit(50)->get());
    }

    /**
     * GET /api/products/{slug}/variants
     *
     * List linked variant products for a given product slug.
     */
    public function indexByProductSlug(string $slug, Request $request)
    {
        $isAdmin = $request->user()?->roles()->exists();
        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);

        $query = Product::query()
            ->select('products.*')
            ->join('product_variants as pv', 'pv.variant_id', '=', 'products.id')
            ->join('products as parent', 'parent.id', '=', 'pv.product_id')
            ->where('parent.slug', $slug);

        if (! $isAdmin) {
            $query->where('products.active', true);
        }

        $query->with([
            'attributes.attribute',
            'attributes.option',
            'prices' => fn ($p) => $p->where('currency_code', $ctx->currency),
            'thumbnailMedia.asset.renditions',
            'galleryMedia.asset.renditions',
        ]);

        $query = $this->applySorting($this->applyFilters($query, $request), $request);

        $variants = $query->get();

        // Attach stock availability (country scope)
        $stockIds = app(StockScopeResolver::class)->forCountry($ctx->country);
        if (count($stockIds) > 0 && $variants->count() > 0) {
            $variantIds = $variants->pluck('id')->map(fn ($id) => (int) $id)->all();

            $rows = DB::table('stock_levels')
                ->select('product_id', DB::raw('sum(quantity)::int as qty_sum'))
                ->whereIn('stock_id', $stockIds)
                ->whereIn('product_id', $variantIds)
                ->groupBy('product_id')
                ->get();

            $stockMap = [];
            foreach ($rows as $r) {
                $stockMap[(int) $r->product_id] = (int) $r->qty_sum;
            }

            foreach ($variants as $product) {
                $qty = (int) ($stockMap[$product->id] ?? 0);
                $product->setAttribute('country_qty', $qty);
                $product->setAttribute('in_stock', $qty > 0);
            }
        }

        return ProductResource::collection($variants);
    }

    /**
     * POST /api/products/{product}/variants
     *
     * Body: { "variant_id": 123 }
     */
    public function store(ProductVariantLinkRequest $request, Product $product)
    {
        $variantId = (int) $request->validated()['variant_id'];

        if ($variantId === (int) $product->id) {
            abort(422, 'A product cannot be linked as its own variant.');
        }

        $product->variants()->syncWithoutDetaching([$variantId]);

        $variant = Product::query()->findOrFail($variantId);

        return ProductResource::make($variant)->response()->setStatusCode(201);
    }

    /**
     * DELETE /api/products/{product}/variants/{variant}
     */
    public function destroy(Product $product, Product $variant)
    {
        $product->variants()->detach($variant->id);

        return response()->json([], 204);
    }
}
