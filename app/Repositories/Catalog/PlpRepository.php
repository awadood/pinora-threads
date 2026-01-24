<?php

namespace App\Repositories\Catalog;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Catalog\Contracts\IPlpRepository;
use App\Repositories\Catalog\Filters\ProductFilters;
use App\Support\ProductListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * PlpRepository
 *
 * Concrete repository for product listing.
 *
 * @author Abdul Wadood
 */
class PlpRepository implements IPlpRepository
{
    public function list(ProductListQuery $q): LengthAwarePaginator
    {
        return $q->profile === ProductFilters::PROFILE_ADMIN
            ? $this->paginateAdmin($q)
            : $this->paginateStorefront($q);
    }

    private function paginateAdmin(ProductListQuery $q): LengthAwarePaginator
    {
        $query = Product::query()->select('products.*')->distinct();

        $this->applyProductFilters($query, $q);
        $this->applyVariantFiltersAsExists($query, $q); // admin may use q/attr/price/in_stock too
        $this->applyPublishableFilter($query, $q);      // admin-only
        $this->applyAdminSorting($query, $q);

        return $query->paginate(
            perPage: $q->perPage,
            columns: ['*'],
            pageName: 'page',
            page: $q->page
        );
    }

    private function paginateStorefront(ProductListQuery $q): LengthAwarePaginator
    {
        /**
         * Storefront strategy:
         * - one row per product
         * - compute selected_variant_id + selected_price_amount (ctx currency)
         * - then hydrate selected variants (thumbnailMedia + prices scoped) for cards
         *
         * Keep your existing LATERAL / correlated approach here.
         */
        $base = Product::query()->select('products.*');

        $this->applyProductFilters($base, $q);
        $this->applyStorefrontSortingAndSelectedVariant($base, $q); // your existing lateral logic lives here

        $paginator = $base->paginate(
            perPage: $q->perPage,
            columns: ['*'],
            pageName: 'page',
            page: $q->page
        );

        // Hydrate selected variant if your existing storefront code expects it.
        // IMPORTANT: load thumbnailMedia instead of mediaAttachments
        $this->hydrateSelectedVariants($paginator, $q);

        return $paginator;
    }

    private function applyProductFilters(Builder $query, ProductListQuery $q): void
    {
        $f = $q->productFilters;

        // storefront usually enforces active=1 implicitly; admin can filter it.
        if ($q->profile === ProductFilters::PROFILE_STOREFRONT) {
            $query->where('products.active', true);
        } elseif (array_key_exists('active', $f) && $f['active'] !== null) {
            $query->where('products.active', $f['active']);
        }

        if ($q->profile === ProductFilters::PROFILE_ADMIN) {
            if (! empty($f['type'])) {
                $query->where('products.type', $f['type']);
            }
            if (! empty($f['type_in']) && is_array($f['type_in'])) {
                $query->whereIn('products.type', $f['type_in']);
            }
            if (! empty($f['sku'])) {
                $query->where('products.sku', $f['sku']);
            }
            if (! empty($f['slug'])) {
                $query->where('products.slug', $f['slug']);
            }
            if (! empty($f['name']) && is_string($f['name'])) {
                $query->where('products.name', 'ilike', '%'.$f['name'].'%');
            }
        }

        if (! empty($f['category_slug'])) {
            $slug = $f['category_slug'];
            $query->whereHas('categories', fn (Builder $c) => $c->where('slug', $slug));
        }

        if (! empty($f['collection_slug'])) {
            $slug = $f['collection_slug'];
            $query->whereHas('collections', fn (Builder $c) => $c->where('slug', $slug));
        }
    }

    /**
     * Variant filters applied as EXISTS conditions so products don't duplicate.
     * This supports admin applying storefront-style filters without returning selected_variant.
     */
    private function applyVariantFiltersAsExists(Builder $query, ProductListQuery $q): void
    {
        $vf = $q->variantFilters;

        $hasQ = isset($vf['q']) && is_string($vf['q']) && trim($vf['q']) !== '';
        $hasAttrs = ! empty($vf['attrs_eq']) || ! empty($vf['attrs_in']);
        $hasPrice = ($vf['price_gte'] ?? null) !== null || ($vf['price_lte'] ?? null) !== null;
        $hasStock = (($vf['in_stock'] ?? null) === true);

        $needsVariant = $hasQ || $hasAttrs || $hasPrice || $hasStock;

        if (! $needsVariant) {
            return;
        }

        // EXISTS: variants matching ONLY q (sku/title/description + attributes/value/options)
        $existsQ = function ($parent) use ($vf) {
            $qText = trim((string) ($vf['q'] ?? ''));
            $needle = '%'.$qText.'%';

            $parent->whereExists(function ($sub) use ($needle) {
                $sub->select(DB::raw(1))
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->where('pv.active', true)
                    ->where(function ($w) use ($needle) {
                        $w->where('pv.sku', 'ilike', $needle)
                            ->orWhere('pv.title', 'ilike', $needle)
                            ->orWhere('pv.description', 'ilike', $needle)

                            // free-text attribute values
                            ->orWhereExists(function ($a) use ($needle) {
                                $a->select(DB::raw(1))
                                    ->from('product_variant_attributes as pva')
                                    ->whereColumn('pva.product_variant_id', 'pv.id')
                                    ->whereNotNull('pva.value')
                                    ->where('pva.value', 'ilike', $needle);
                            })

                            // selectable attribute options
                            ->orWhereExists(function ($o) use ($needle) {
                                $o->select(DB::raw(1))
                                    ->from('product_variant_attributes as pva2')
                                    ->join('attribute_options as ao', 'ao.id', '=', 'pva2.option_id')
                                    ->whereColumn('pva2.product_variant_id', 'pv.id')
                                    ->where('ao.value', 'ilike', $needle);
                            });
                    });
            });
        };

        // EXISTS: variants matching NON-q constraints (attrs/price/in_stock)
        $existsNonQ = function ($parent) use ($q, $vf, $hasAttrs, $hasPrice, $hasStock) {
            $parent->whereExists(function ($sub) use ($q, $vf, $hasAttrs, $hasPrice, $hasStock) {
                $sub->select(DB::raw(1))
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->where('pv.active', true);

                // Attribute eq
                if ($hasAttrs && ! empty($vf['attrs_eq'])) {
                    foreach ($vf['attrs_eq'] as $code => $value) {
                        $sub->whereExists(function ($a) use ($code, $value) {
                            $a->select(DB::raw(1))
                                ->from('product_variant_attributes as pva')
                                ->join('attributes as at', 'at.id', '=', 'pva.attribute_id')
                                ->leftJoin('attribute_options as ao', 'ao.id', '=', 'pva.option_id')
                                ->whereColumn('pva.product_variant_id', 'pv.id')
                                ->where('at.code', $code)
                                ->where(function ($w) use ($value) {
                                    $w->where('pva.value', $value)->orWhere('ao.value', $value);
                                });
                        });
                    }
                }

                // Attribute in
                if ($hasAttrs && ! empty($vf['attrs_in'])) {
                    foreach ($vf['attrs_in'] as $code => $values) {
                        $values = is_array($values) ? $values : [];
                        if (! count($values)) {
                            continue;
                        }

                        $sub->whereExists(function ($a) use ($code, $values) {
                            $a->select(DB::raw(1))
                                ->from('product_variant_attributes as pva')
                                ->join('attributes as at', 'at.id', '=', 'pva.attribute_id')
                                ->leftJoin('attribute_options as ao', 'ao.id', '=', 'pva.option_id')
                                ->whereColumn('pva.product_variant_id', 'pv.id')
                                ->where('at.code', $code)
                                ->where(function ($w) use ($values) {
                                    $w->whereIn('pva.value', $values)->orWhereIn('ao.value', $values);
                                });
                        });
                    }
                }

                // Price filters (ctx currency, any variant)
                if ($hasPrice) {
                    $sub->join('product_variant_prices as pvp', function ($j) use ($q) {
                        $j->on('pvp.product_variant_id', '=', 'pv.id')
                            ->where('pvp.currency_code', '=', $q->currencyCode);
                    });

                    if (($vf['price_gte'] ?? null) !== null) {
                        $sub->where('pvp.amount', '>=', $vf['price_gte']);
                    }
                    if (($vf['price_lte'] ?? null) !== null) {
                        $sub->where('pvp.amount', '<=', $vf['price_lte']);
                    }
                }

                // In-stock (purchasable)
                if ($hasStock) {
                    $stockIds = $q->stockIds;
                    $sub->whereExists(function ($s) use ($stockIds) {
                        $s->select(DB::raw(1))
                            ->from('stock_levels as sl')
                            ->whereColumn('sl.variant_id', 'pv.id')
                            ->whereIn('sl.stock_id', $stockIds)
                            ->where('sl.quantity', '>', 0);
                    });
                }
            });
        };

        // Product match part for q (name/slug only, as locked)
        $productQNameSlug = function (Builder $w) use ($vf) {
            $needle = '%'.trim((string) $vf['q']).'%';
            $w->where('products.name', 'ilike', $needle)
                ->orWhere('products.slug', 'ilike', $needle);
        };

        /**
         * Compose correctly:
         * - If there are NON-q constraints, they must always hold (via existsNonQ).
         * - q must hold too, but q can be satisfied by either product fields OR variant fields.
         * - If q is absent, only non-q constraints apply.
         */
        if ($hasAttrs || $hasPrice || $hasStock) {
            $existsNonQ($query);

            if ($hasQ) {
                $query->where(function (Builder $w) use ($productQNameSlug, $existsQ) {
                    $w->where(function (Builder $p) use ($productQNameSlug) {
                        $productQNameSlug($p);
                    })->orWhere(function (Builder $v) use ($existsQ) {
                        $existsQ($v);
                    });
                });
            }

            return;
        }

        // Only-q case: (product match) OR (variant match)
        if ($hasQ) {
            $query->where(function (Builder $w) use ($productQNameSlug, $existsQ) {
                $w->where(function (Builder $p) use ($productQNameSlug) {
                    $productQNameSlug($p);
                })->orWhere(function (Builder $v) use ($existsQ) {
                    $existsQ($v);
                });
            });
        }
    }

    /**
     * Admin-only publishable filter (default variant must have thumbnail AND gallery >= 7).
     */
    private function applyPublishableFilter(Builder $query, ProductListQuery $q): void
    {
        if ($q->profile !== ProductFilters::PROFILE_ADMIN) {
            return;
        }

        $want = $q->productFilters['publishable'] ?? null;
        if ($want === null) {
            return;
        }

        // publishable condition as EXISTS clause
        $publishableExists = function (Builder $qb) {
            $qb->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->where('pv.default', true)
                    ->whereExists(function ($m) {
                        $m->select(DB::raw(1))
                            ->from('media_attachments as ma')
                            ->whereColumn('ma.owner_id', 'pv.id')
                            ->where('ma.owner_type', 'App\\Models\\ProductVariant')
                            ->where('ma.role', 'thumbnail')
                            ->where('ma.is_primary', true);
                    })

                    // thumbnail exists
                    ->whereExists(function ($m) {
                        $m->select(DB::raw(1))
                            ->from('media_attachments as ma')
                            ->whereColumn('ma.owner_id', 'pv.id')
                            ->where('ma.owner_type', 'App\\Models\\ProductVariant')
                            ->where('ma.role', 'thumbnail');
                    })

                    // gallery count >= 7
                    ->whereRaw("(
                        select count(*) from media_attachments ma2
                        where ma2.owner_type = 'App\\\\Models\\\\ProductVariant'
                          and ma2.owner_id = pv.id
                          and ma2.role = 'gallery'
                    ) >= 7");
            });
        };

        if ($want === true) {
            $publishableExists($query);
        } else {
            // not publishable => NOT EXISTS publishable condition
            $query->whereNot(function (Builder $w) use ($publishableExists) {
                $publishableExists($w);
            });
        }
    }

    private function applyAdminSorting(Builder $query, ProductListQuery $q): void
    {
        switch ($q->sort) {
            case 'name':
                $query->orderBy('products.name', 'asc');
                break;
            case '-name':
                $query->orderBy('products.name', 'desc');
                break;

            case 'updated_at':
                $query->orderBy('products.updated_at', 'asc');
                break;
            case '-updated_at':
                $query->orderBy('products.updated_at', 'desc');
                break;

            case 'active':
                $query->orderBy('products.active', 'asc');
                break;
            case '-active':
                $query->orderBy('products.active', 'desc');
                break;

            case 'type':
                $query->orderBy('products.type', 'asc');
                break;
            case '-type':
                $query->orderBy('products.type', 'desc');
                break;

            default:
                $query->orderBy('products.updated_at', 'desc');
        }
    }

    private function applyStorefrontSortingAndSelectedVariant(Builder $query, ProductListQuery $q): void
    {
        // Join the LATERAL subquery that chooses ONE selected variant per product.
        ['sql' => $sql, 'bindings' => $bindings] = $this->selectedVariantLateralSql($q);

        // INNER JOIN LATERAL: products without a matching selected variant are excluded from PLP.
        $query->join(
            DB::raw("LATERAL ($sql) sv"),
            DB::raw('true'),
            '=',
            DB::raw('true')
        );

        // Bindings belong to the JOIN clause.
        $query->addBinding($bindings, 'join');

        // Expose computed fields for resources/sorting.
        $query->addSelect([
            'sv.selected_variant_id',
            'sv.selected_price_amount',
        ]);

        // Apply storefront sorts.
        switch ($q->sort) {
            case 'name':
                $query->orderBy('products.name', 'asc');
                break;

            case '-name':
                $query->orderBy('products.name', 'desc');
                break;

            case 'price':
                $query->orderBy('sv.selected_price_amount', 'asc');
                break;

            case '-price':
                $query->orderBy('sv.selected_price_amount', 'desc');
                break;

            case 'newest':
            default:
                $query->orderBy('products.created_at', 'desc');
                break;
        }

        // Tie-breaker for stable pagination.
        $query->orderBy('products.id', 'desc');
    }

    /**
     * Returns LATERAL SQL that picks exactly one "selected variant" per product.
     *
     * Rules (locked):
     * - If no variant constraints: select default variant.
     * - If variant constraints: select best matching variant (deterministic).
     * - Price is variant price in ctx currency.
     * - In-stock means stock_levels.quantity > 0 in ANY active stock within ctx country (stockIds passed in).
     */
    private function selectedVariantLateralSql(ProductListQuery $q): array
    {
        $vf = $q->variantFilters;

        // Bindings order must match placeholders in $sql.
        // Placeholder #1: stockIds (int[])
        // Placeholder #2: currency_code
        $bindings = [];
        $bindings[] = '{'.implode(',', array_map('intval', $q->stockIds)).'}'; // for ANY(?::int[])
        $bindings[] = $q->currencyCode;                                       // for pvp.currency_code = ?

        $sql = '
            SELECT
                pv.id AS selected_variant_id,
                pvp.amount AS selected_price_amount,
                EXISTS (
                    SELECT 1
                    FROM stock_levels sl
                    WHERE sl.variant_id = pv.id
                    AND sl.stock_id = ANY (?::int[])
                    AND sl.quantity > 0
                ) AS purchasable
            FROM product_variants pv
            JOIN product_variant_prices pvp
            ON pvp.product_variant_id = pv.id
            AND pvp.currency_code = ?
            WHERE pv.product_id = products.id
            AND pv.active = true
        ';

        // Default-variant-only path when there are no variant constraints.
        if (! $q->hasVariantConstraints) {
            $sql .= ' AND pv.default = true ';
            $sql .= ' ORDER BY pv.id ASC LIMIT 1 ';

            return ['sql' => $sql, 'bindings' => $bindings];
        }

        // --- Variant constraints path (match ANY variant) ---

        $qText = $vf['q'] ?? null;
        $hasQ = is_string($qText) && trim($qText) !== '';

        $onlyQ =
            $hasQ
            && empty($vf['attrs_eq'])
            && empty($vf['attrs_in'])
            && (($vf['price_gte'] ?? null) === null)
            && (($vf['price_lte'] ?? null) === null)
            && (($vf['in_stock'] ?? null) !== true);

        /**
         * 1) q across variant (+ attributes/options)
         * Special storefront behavior when ONLY q is present:
         * - If product matches name/slug, allow selecting the default variant even if no variant matches q.
         * - Otherwise require variant-q match.
         */
        if ($hasQ) {
            $like = '%'.trim((string) $qText).'%';

            if ($onlyQ) {
                $sql .= '
                    AND (
                        (
                            (products.name ILIKE ? OR products.slug ILIKE ?)
                            AND pv.default = true
                        )
                        OR
                        (
                            pv.sku ILIKE ?
                            OR pv.title ILIKE ?
                            OR pv.description ILIKE ?
                            OR EXISTS (
                                SELECT 1
                                FROM product_variant_attributes pva
                                WHERE pva.product_variant_id = pv.id
                                AND pva.value IS NOT NULL
                                AND pva.value ILIKE ?
                            )
                            OR EXISTS (
                                SELECT 1
                                FROM product_variant_attributes pva2
                                JOIN attribute_options ao ON ao.id = pva2.option_id
                                WHERE pva2.product_variant_id = pv.id
                                AND ao.value ILIKE ?
                            )
                        )
                    )
                ';

                // product match (2)
                $bindings[] = $like; // products.name
                $bindings[] = $like; // products.slug

                // variant match (5)
                $bindings[] = $like; // pv.sku
                $bindings[] = $like; // pv.title
                $bindings[] = $like; // pv.description
                $bindings[] = $like; // pva.value
                $bindings[] = $like; // ao.value
            } else {
                $sql .= '
                    AND (
                        pv.sku ILIKE ?
                        OR pv.title ILIKE ?
                        OR pv.description ILIKE ?
                        OR EXISTS (
                            SELECT 1
                            FROM product_variant_attributes pva
                            WHERE pva.product_variant_id = pv.id
                            AND pva.value IS NOT NULL
                            AND pva.value ILIKE ?
                        )
                        OR EXISTS (
                            SELECT 1
                            FROM product_variant_attributes pva2
                            JOIN attribute_options ao ON ao.id = pva2.option_id
                            WHERE pva2.product_variant_id = pv.id
                            AND ao.value ILIKE ?
                        )
                    )
                ';

                $bindings[] = $like; // pv.sku
                $bindings[] = $like; // pv.title
                $bindings[] = $like; // pv.description
                $bindings[] = $like; // pva.value
                $bindings[] = $like; // ao.value
            }
        }

        // 2) attr.<code>.eq
        $attrsEq = $vf['attrs_eq'] ?? [];
        if (is_array($attrsEq) && ! empty($attrsEq)) {
            foreach ($attrsEq as $code => $value) {
                if (! is_string($code) || $code === '' || ! is_string($value) || trim($value) === '') {
                    continue;
                }

                $bindings[] = $code;
                $bindings[] = $value;
                $bindings[] = $value;

                $sql .= '
                    AND EXISTS (
                        SELECT 1
                        FROM product_variant_attributes pva
                        JOIN attributes at ON at.id = pva.attribute_id
                        LEFT JOIN attribute_options ao ON ao.id = pva.option_id
                        WHERE pva.product_variant_id = pv.id
                        AND at.code = ?
                        AND (
                            pva.value = ?
                            OR ao.value = ?
                        )
                    )
                ';
            }
        }

        // 3) attr.<code>.in
        $attrsIn = $vf['attrs_in'] ?? [];
        if (is_array($attrsIn) && ! empty($attrsIn)) {
            foreach ($attrsIn as $code => $values) {
                if (! is_string($code) || $code === '' || ! is_array($values) || empty($values)) {
                    continue;
                }

                $values = array_values(array_filter(array_map('trim', $values), static fn ($v) => $v !== ''));
                if (! count($values)) {
                    continue;
                }

                $arr = '{'.implode(',', array_map(static fn ($v) => str_replace('"', '\"', $v), $values)).'}';

                $bindings[] = $code;
                $bindings[] = $arr;

                $sql .= '
                    AND EXISTS (
                        SELECT 1
                        FROM product_variant_attributes pva
                        JOIN attributes at ON at.id = pva.attribute_id
                        LEFT JOIN attribute_options ao ON ao.id = pva.option_id
                        WHERE pva.product_variant_id = pv.id
                        AND at.code = ?
                        AND (
                            pva.value = ANY (?::text[])
                            OR ao.value = ANY (?::text[])
                        )
                    )
                ';

                // same array binding used twice
                $bindings[] = $arr;
            }
        }

        // 4) price range (ctx currency, any variant)
        if (($vf['price_gte'] ?? null) !== null && is_numeric($vf['price_gte'])) {
            $bindings[] = (float) $vf['price_gte'];
            $sql .= ' AND pvp.amount >= ? ';
        }
        if (($vf['price_lte'] ?? null) !== null && is_numeric($vf['price_lte'])) {
            $bindings[] = (float) $vf['price_lte'];
            $sql .= ' AND pvp.amount <= ? ';
        }

        // 5) in_stock.eq=1 (purchasable only)
        if (($vf['in_stock'] ?? null) === true) {
            $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM stock_levels sl2
                    WHERE sl2.variant_id = pv.id
                    AND sl2.stock_id = ANY (?::int[])
                    AND sl2.quantity > 0
                )
            ';
            // reuse stockIds
            $bindings[] = '{'.implode(',', array_map('intval', $q->stockIds)).'}';
        }

        // Deterministic selection
        $sql .= '
            ORDER BY
                purchasable DESC,
                pvp.amount ASC,
                pv.id ASC
            LIMIT 1
        ';

        return ['sql' => $sql, 'bindings' => $bindings];
    }

    private function hydrateSelectedVariants(LengthAwarePaginator $paginator, ProductListQuery $q): void
    {
        $items = $paginator->items();
        $variantIds = [];

        foreach ($items as $product) {
            if (! empty($product->selected_variant_id)) {
                $variantIds[] = (int) $product->selected_variant_id;
            }
        }

        $variantIds = array_values(array_unique($variantIds));
        if (count($variantIds) === 0) {
            return;
        }

        // Load variants + media + price (ctx currency) + stock summary (any stock in country)
        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->with([
                // PLP needs thumbnail only - later renditions or other media can be addded
                'thumbnailMedia.asset',
                'prices' => fn ($p) => $p->where('currency_code', $q->currencyCode),
            ])
            ->get()
            ->keyBy('id');

        // Stock availability (any active stock in country)
        $stockIds = $q->stockIds;
        $stockMap = [];
        if (! empty($stockIds)) {
            $rows = DB::table('stock_levels')
                ->select('variant_id', DB::raw('sum(quantity)::int as qty_sum'))
                ->whereIn('stock_id', $stockIds)
                ->whereIn('variant_id', $variantIds)
                ->groupBy('variant_id')
                ->get();

            foreach ($rows as $r) {
                $stockMap[(int) $r->variant_id] = (int) $r->qty_sum;
            }
        }

        foreach ($items as $product) {
            $vid = (int) ($product->selected_variant_id ?? 0);
            if ($vid <= 0) {
                continue;
            }

            $variant = $variants->get($vid);
            if (! $variant) {
                continue;
            }

            // Attach computed availability (total qty across country stocks)
            $qty = $stockMap[$vid] ?? 0;
            $variant->setAttribute('country_qty', $qty);
            $variant->setAttribute('in_stock', $qty > 0);

            // Attach relation-like property for resources
            $product->setRelation('selectedVariant', $variant);

            // Attach selected price amount from lateral (already on product row)
            $product->setAttribute('selected_price_amount', $product->selected_price_amount);
        }
    }
}
