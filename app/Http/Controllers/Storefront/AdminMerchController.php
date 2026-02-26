<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\MerchSectionItemsRequest;
use App\Http\Requests\Storefront\MerchSectionQueryConfigRequest;
use App\Http\Requests\Storefront\MerchSectionUpsertRequest;
use App\Models\MerchSection;
use App\Models\MerchSectionItem;
use App\Repositories\Catalog\Filters\ProductFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * AdminMerchController (v1)
 *
 * Supported features:
 * - CRUD sections
 * - Assign curated items (products/collections/categories/attribute options) with ordering
 * - Configure query-mode sections by submitting PLP-style query params
 *
 * Locked rules:
 * - Sections are homogeneous (item_type).
 * - Admin never writes query_payload directly.
 * - query_payload is derived from ProductFilters::parse(...storefront profile) and stored normalized.
 */
class AdminMerchController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = MerchSection::query()->with(['items'])
            ->orderBy('surface')
            ->orderBy('sort')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(MerchSectionUpsertRequest $request): JsonResponse
    {
        $section = MerchSection::create($request->validated());

        return response()->json(['data' => $section], 201);
    }

    public function update(MerchSectionUpsertRequest $request, MerchSection $section): JsonResponse
    {
        $section->fill($request->validated());
        $section->save();

        return response()->json(['data' => $section]);
    }

    /**
     * Replace curated items (ordered).
     *
     * Payload:
     * {
     *   "items": [
     *     {"item_id": 12, "position": 1, "active": true},
     *     ...
     *   ]
     * }
     *
     * LOCKED:
     * - items must match section.item_type
     * - curated-only; query mode ignores items
     */
    public function setItems(MerchSectionItemsRequest $request, MerchSection $section): JsonResponse
    {
        if ($section->mode !== 'curated') {
            return response()->json([
                'message' => "Section is mode='{$section->mode}'. Items are only applicable for mode='curated'.",
            ], 422);
        }

        $payload = $request->validated();
        $items = $payload['items'];

        DB::transaction(function () use ($section, $items) {
            MerchSectionItem::query()->where('merch_section_id', $section->id)->delete();

            foreach ($items as $row) {
                MerchSectionItem::create([
                    'merch_section_id' => $section->id,
                    'item_type' => $section->item_type,
                    'item_id' => (int) $row['item_id'],
                    'position' => (int) ($row['position'] ?? 1),
                    'active' => array_key_exists('active', $row) ? (bool) $row['active'] : true,
                ]);
            }
        });

        return response()->json(['message' => 'Items updated.']);
    }

    /**
     * Save query configuration for mode=query.
     *
     * LOCKED:
     * - We do NOT allow free-text 'q' in merchandising query configs.
     * - We store a normalized, whitelist-safe payload derived by ProductFilters::parse() in storefront profile.
     */
    public function setQueryConfig(
        MerchSectionQueryConfigRequest $request,
        MerchSection $section,
        ProductFilters $filters
    ): JsonResponse {
        if ($section->mode !== 'query') {
            return response()->json([
                'message' => "Section is mode='{$section->mode}'. Query config is only applicable for mode='query'.",
            ], 422);
        }

        if ($section->item_type !== 'product') {
            return response()->json([
                'message' => "Query mode is only supported for item_type='product' and mode='query'.",
            ], 422);
        }

        // Hard policy: no q for merchandising
        if ($request->filled('q')) {
            return response()->json([
                'message' => "Free-text 'q' is not allowed for merchandising sections. Use category/collection/attr/price/in_stock filters instead.",
            ], 422);
        }

        // Normalize using existing filter parser (storefront profile).
        $parsed = $filters->parse($request, ProductFilters::PROFILE_STOREFRONT);

        // Persist only the normalized echo/filter/sort (no raw).
        $queryPayload = [
            'sort' => $parsed['sort'],
            'filter' => $parsed['echo']['filter'] ?? [],
        ];

        $section->query_payload = $queryPayload;
        $section->save();

        return response()->json($section->fresh());
    }
}
