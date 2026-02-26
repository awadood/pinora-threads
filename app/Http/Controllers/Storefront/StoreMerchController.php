<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\CollectionResource;
use App\Http\Resources\Catalog\ProductResource;
use App\Http\Resources\Catalog\AttributeOptionResource;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Collection;
use App\Models\MerchSection;
use App\Repositories\Catalog\Contracts\IPlpRepository;
use App\Repositories\Catalog\Filters\ProductFilters;
use App\Repositories\Storefront\Contracts\IMerchandisingRepository;
use App\Support\ProductListQuery;
use App\Support\StockScopeResolver;
use App\Support\Storefront\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

/**
 * StoreMerchController
 *
 * ONE endpoint for all storefront merchandising sections.
 *
 * Rules (locked):
 * - StoreContext determines country/currency; no request override allowed.
 * - Sections are homogeneous.
 * - Mode=curated => use explicit items (ordered).
 * - Mode=query   => execute PLP listing engine using stored normalized query_payload.
 * - No hidden fallback behavior: missing/disabled/unscheduled section => 404.
 *
 * Notes (v1):
 * - item_type=product supports curated + query
 * - item_type=collection supports curated only
 * - item_type=category supports curated only
 * - item_type=attribute supports curated only (item_id references attribute_options.id)
 *
 * @author Abdul Wadood
 */
class StoreMerchController extends Controller
{
    public function __construct(
        protected IPlpRepository $plp,
        protected IMerchandisingRepository $merch,
        protected StockScopeResolver $stockResolver
    ) {}

    public function show(Request $request, string $code): JsonResponse
    {
        /** @var StoreContext $ctx */
        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);

        $section = $this->resolveSectionForCtx($code, $ctx->country);

        if (! $section) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $limit = (int) ($request->query('limit') ?? $section->default_limit);
        $limit = max(1, min(24, $limit));

        // --- Dispatch by item_type ---
        if ($section->item_type === 'product') {
            $productsPaginator = $section->mode === 'curated'
                ? $this->resolveCuratedProducts($section, $ctx, $limit, $this->plp)
                : $this->resolveQueryProducts($section, $ctx, $limit, $this->plp);

            return response()->json([
                'section' => $this->sectionPayload($section),
                'products' => ProductResource::collection($productsPaginator),
            ]);
        }

        if ($section->item_type === 'collection') {
            if ($section->mode !== 'curated') {
                return response()->json([
                    'message' => "Section mode='{$section->mode}' not supported for item_type='collection'.",
                ], 422);
            }

            $collections = $this->resolveCuratedCollections($section, $ctx->country, $limit);

            return response()->json([
                'section' => $this->sectionPayload($section),
                'collections' => CollectionResource::collection($collections),
            ]);
        }

        if ($section->item_type === 'category') {
            if ($section->mode !== 'curated') {
                return response()->json([
                    'message' => "Section mode='{$section->mode}' not supported for item_type='category'.",
                ], 422);
            }

            $categories = $this->resolveCuratedCategories($section, $limit);

            return response()->json([
                'section' => $this->sectionPayload($section),
                'categories' => CategoryResource::collection($categories),
            ]);
        }

        if ($section->item_type === 'attribute') {
            if ($section->mode !== 'curated') {
                return response()->json([
                    'message' => "Section mode='{$section->mode}' not supported for item_type='attribute'.",
                ], 422);
            }

            $attributeOptionItems = $this->resolveCuratedAttributeOptions($section, $limit);

            return response()->json([
                'section' => $this->sectionPayload($section),
                'attribute_options' => AttributeOptionResource::collection($attributeOptionItems),
            ]);
        }

        return response()->json([
            'message' => "Section item_type='{$section->item_type}' not supported.",
        ], 422);
    }

    private function sectionPayload(MerchSection $section): array
    {
        return [
            'id' => $section->id,
            'code' => $section->code,
            'name' => $section->name,
            'surface' => $section->surface,
            'mode' => $section->mode,
            'item_type' => $section->item_type,
            'meta' => $section->meta,
            'default_limit' => $section->default_limit,
        ];
    }

    private function resolveSectionForCtx(string $code, string $country): ?MerchSection
    {
        // Prefer country-specific, then global fallback.
        $base = MerchSection::query()
            ->where('code', $code)
            ->active()
            ->withinSchedule();

        $specific = (clone $base)->where('country_code', $country)->first();
        if ($specific) {
            return $specific;
        }

        return $base->whereNull('country_code')->first();
    }

    private function resolveCuratedProducts(MerchSection $section, StoreContext $ctx, int $limit, IPlpRepository $plp): LengthAwarePaginator
    {
        $itemIds = $section->items()
            ->where('active', true)
            ->orderBy('position')
            ->limit($limit)
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($itemIds) === 0) {
            return new LengthAwarePaginator([], 0, $limit, 1);
        }

        $dto = new ProductListQuery(
            profile: ProductFilters::PROFILE_STOREFRONT,
            page: 1,
            perPage: $limit,
            sort: 'newest',
            productFilters: [
                'ids_in' => $itemIds, // INTERNAL
            ],
            detailFilters: [],
            hasDetailConstraints: false,
            countryCode: $ctx->country,
            currencyCode: $ctx->currency,
            stockIds: $this->stockResolver->forCountry($ctx->country),
            appliedEcho: [],
        );

        $paginator = $plp->list($dto);

        // Preserve curated order (whereIn does not preserve order).
        $map = array_flip($itemIds);
        $sorted = collect($paginator->items())->sortBy(fn ($p) => $map[$p->id] ?? 999999)->values();

        return new LengthAwarePaginator(
            $sorted->all(),
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function resolveQueryProducts(MerchSection $section, StoreContext $ctx, int $limit, IPlpRepository $plp): LengthAwarePaginator
    {
        $payload = is_array($section->query_payload) ? $section->query_payload : [];
        $sort = (string) ($payload['sort'] ?? 'newest');
        $filter = is_array($payload['filter'] ?? null) ? $payload['filter'] : [];

        $dto = new ProductListQuery(
            profile: ProductFilters::PROFILE_STOREFRONT,
            page: 1,
            perPage: $limit,
            sort: $sort,
            productFilters: [
                'category_slug' => $filter['category.slug.eq'] ?? null,
                'collection_slug' => $filter['collection.slug.eq'] ?? null,
            ],
            detailFilters: [
                // q intentionally not supported in merchandising
                'q' => null,
                'attrs_eq' => $filter['attr.eq'] ?? [],
                'attrs_in' => $filter['attr.in'] ?? [],
                'price_gte' => $filter['price.gte'] ?? null,
                'price_lte' => $filter['price.lte'] ?? null,
                'in_stock' => array_key_exists('in_stock.eq', $filter) ? (bool) $filter['in_stock.eq'] : null,
            ],
            hasDetailConstraints: ! empty($filter['attr.eq'])
                || ! empty($filter['attr.in'])
                || array_key_exists('price.gte', $filter)
                || array_key_exists('price.lte', $filter)
                || ! empty($filter['in_stock.eq']),
            countryCode: $ctx->country,
            currencyCode: $ctx->currency,
            stockIds: $this->stockResolver->forCountry($ctx->country),
            appliedEcho: [],
        );

        return $plp->list($dto);
    }

    /**
     * Curated collections (v1).
     *
     * Rule:
     * - Only collections explicitly mapped to ctx country are returned.
     * - Order is preserved by merch_section_items.position.
     */
    private function resolveCuratedCollections(MerchSection $section, string $countryCode, int $limit)
    {
        $ids = $section->items()
            ->where('active', true)
            ->orderBy('position')
            ->limit($limit)
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($ids) === 0) {
            return collect();
        }

        $rows = Collection::query()
            ->whereIn('id', $ids)
            ->where('active', true)
            ->whereHas('countries', fn ($q) => $q->where('country_code', $countryCode))
            ->with([
                'thumbnailMedia.asset.renditions',
                'heroMedia.asset.renditions',
            ])
            ->get()
            ->keyBy('id');

        // Preserve curated order
        $out = [];
        foreach ($ids as $id) {
            if (isset($rows[$id])) {
                $out[] = $rows[$id];
            }
        }

        return collect($out);
    }

    /**
     * Curated categories (v1).
     *
     * Rule:
     * - Categories are not country-scoped in v1.
     * - Order is preserved by merch_section_items.position.
     */
    private function resolveCuratedCategories(MerchSection $section, int $limit)
    {
        $ids = $section->items()
            ->where('active', true)
            ->orderBy('position')
            ->limit($limit)
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($ids) === 0) {
            return collect();
        }

        $rows = Category::query()
            ->whereIn('id', $ids)
            ->where('active', true)
            ->with([
                // keep in sync with your CategoryResource needs
                'thumbnailMedia.asset.renditions',
                'heroMedia.asset.renditions',
            ])
            ->get()
            ->keyBy('id');

        // Preserve curated order
        $out = [];
        foreach ($ids as $id) {
            if (isset($rows[$id])) {
                $out[] = $rows[$id];
            }
        }

        return collect($out);
    }

    /**
     * Curated attribute options (v1).
     *
     * Rule:
     * - Order is preserved by merch_section_items.position.
     * - Only options belonging to active attributes are returned.
     */
    private function resolveCuratedAttributeOptions(MerchSection $section, int $limit)
    {
        $ids = $section->items()
            ->where('active', true)
            ->orderBy('position')
            ->limit($limit)
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($ids) === 0) {
            return collect();
        }

        $rows = AttributeOption::query()
            ->whereIn('id', $ids)
            ->whereHas('attribute', fn ($q) => $q->where('active', true))
            ->with(['attribute', 'thumbnailMedia.asset.renditions'])
            ->get()
            ->keyBy('id');

        // Preserve curated order
        $out = [];
        foreach ($ids as $id) {
            if (isset($rows[$id])) {
                $out[] = $rows[$id];
            }
        }

        return collect($out);
    }
}
