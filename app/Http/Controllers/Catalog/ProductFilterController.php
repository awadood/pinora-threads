<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Collection;
use App\Support\Storefront\StoreContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * ProductFilterController
 *
 * Provides filters for product listings.
 *
 * @author Abdul Wadood
 */
class ProductFilterController extends Controller
{
    public function index(Request $request)
    {
        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);

        // Allowed filters/sorts (locked)
        $capabilities = [
            'filters' => [
                'q' => [
                    'type' => 'search',
                    'param' => 'q',
                    'notes' => 'Searches product name/slug/sku/description OR any product attributes/options.',
                ],
                'category' => [
                    'type' => 'single',
                    'param' => 'filter[category.slug.eq]',
                ],
                'collection' => [
                    'type' => 'single',
                    'param' => 'filter[collection.slug.eq]',
                ],
                'attributes' => [
                    'type' => 'dynamic',
                    'param_patterns' => [
                        'eq' => 'filter[attr.{code}.eq]',
                        'in' => 'filter[attr.{code}.in]',
                    ],
                    'notes' => 'Matches if the product attributes match.',
                ],
                'price' => [
                    'type' => 'range',
                    'params' => [
                        'min' => 'filter[price.gte]',
                        'max' => 'filter[price.lte]',
                    ],
                    'notes' => 'Product price in store context currency.',
                ],
                'in_stock' => [
                    'type' => 'boolean',
                    'param' => 'filter[in_stock.eq]',
                    'truthy' => ['1', 'true', 'yes', 'on'],
                    'notes' => 'Purchasable = stock_levels.quantity > 0 in ANY active stock within ctx country.',
                ],
            ],

            'sorts' => ['newest', 'name', '-name', 'price', '-price'],

            'pagination' => [
                'params' => ['page', 'per_page'],
                'style' => 'length_aware',
            ],
        ];

        $priceBounds = [
            'currency_code' => $ctx->currency,
            'min' => $ctx->currency === config('storefront.default_currency') ? 150 : 3000,
            'max' => $ctx->currency === config('storefront.default_currency') ? 350 : 65000,
            'step' => $ctx->currency === config('storefront.default_currency') ? 10 : 100,
        ];

        // Attributes (active only) + options (for select)
        $attributes = Attribute::query()
            ->where('active', true)
            ->where('type', 'select')
            ->orderBy('id')
            ->with(['options' => fn ($q) => $q->orderBy('sort')])
            ->get()
            ->map(function ($a) {
                return [
                    'code' => $a->code,
                    'label' => $a->label,
                    'type' => $a->type, // text|select
                    'options' => $a->type === 'select'
                        ? $a->options->map(fn ($o) => [
                            'value' => $o->value,
                            'sort' => (int) $o->sort,
                        ])->values()
                        : [],
                ];
            })
            ->values();

        // Optional helpers for UI dropdowns (no facets/counts)
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'sort'])
            ->where('active', true)
            ->whereNotNull('parent_id')
            ->orderBy('parent_id')
            ->orderBy('sort')
            ->get()
            ->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'parent_id' => $c->parent_id ? (int) $c->parent_id : null,
                'sort' => (int) $c->sort,
            ])
            ->values();

        $collections = Collection::query()
            ->select(['id', 'name', 'slug', 'sort'])
            ->where('active', true)
            ->orderBy('sort')
            ->get()
            ->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'sort' => (int) $c->sort,
            ])
            ->values();

        return response()->json([
            'capabilities' => $capabilities,
            'price_bounds' => $priceBounds,
            'attributes' => $attributes,
            'categories' => $categories,
            'collections' => $collections,
        ]);
    }
}
