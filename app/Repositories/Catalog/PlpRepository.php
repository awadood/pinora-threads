<?php

namespace App\Repositories\Catalog;

use App\Models\Product;
use App\Repositories\Catalog\Contracts\IPlpRepository;
use App\Repositories\Catalog\Filters\ProductFilters;
use App\Support\ProductListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * PlpRepository
 *
 * Product listing implementation (storefront + admin).
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
        $this->applyDetailFilters($query, $q);
        $this->applyPublishableFilter($query, $q);
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
        $query = Product::query()
            ->select('products.*')
            ->distinct()
            ->withCount(['variants as variants_count' => fn ($v) => $v->where('active', true)])
            ->with([
                'prices' => fn ($p) => $p->where('currency_code', $q->currencyCode),
                'thumbnailMedia.asset.renditions',
            ]);

        $this->applyProductFilters($query, $q);
        $this->applyDetailFilters($query, $q);
        $this->applyStorefrontSorting($query, $q);

        return $query->paginate(
            perPage: $q->perPage,
            columns: ['*'],
            pageName: 'page',
            page: $q->page
        );
    }

    private function applyProductFilters(Builder $query, ProductListQuery $q): void
    {
        $f = $q->productFilters;

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

        if (! empty($f['ids_in']) && is_array($f['ids_in'])) {
            $query->whereIn('products.id', $f['ids_in']);
        }
    }

    private function applyDetailFilters(Builder $query, ProductListQuery $q): void
    {
        $f = $q->detailFilters;
        $currency = $q->currencyCode;
        $stockIds = $q->stockIds;

        if (! empty($f['q'])) {
            $term = trim((string) $f['q']);
            $like = '%'.$term.'%';

            $query->where(function (Builder $w) use ($like) {
                $w->where('products.name', 'ilike', $like)
                    ->orWhere('products.slug', 'ilike', $like)
                    ->orWhere('products.sku', 'ilike', $like)
                    ->orWhere('products.description', 'ilike', $like)
                    ->orWhereExists(function ($sub) use ($like) {
                        $sub->selectRaw('1')
                            ->from('product_attributes as pa')
                            ->join('attributes as a', 'a.id', '=', 'pa.attribute_id')
                            ->leftJoin('attribute_options as ao', 'ao.id', '=', 'pa.option_id')
                            ->whereColumn('pa.product_id', 'products.id')
                            ->where(function ($qq) use ($like) {
                                $qq->where('a.label', 'ilike', $like)
                                    ->orWhere('a.code', 'ilike', $like)
                                    ->orWhere('pa.value', 'ilike', $like)
                                    ->orWhere('ao.value', 'ilike', $like);
                            });
                    });
            });
        }

        if (! empty($f['attrs_eq'])) {
            foreach ($f['attrs_eq'] as $code => $value) {
                $values = is_array($value) ? $value : [$value];
                $this->applyAttributeFilter($query, $code, $values);
            }
        }

        if (! empty($f['attrs_in'])) {
            foreach ($f['attrs_in'] as $code => $values) {
                $this->applyAttributeFilter($query, $code, $values);
            }
        }

        if ($f['price_gte'] !== null || $f['price_lte'] !== null) {
            $query->whereExists(function ($sub) use ($currency, $f) {
                $sub->selectRaw('1')
                    ->from('product_prices as pp')
                    ->whereColumn('pp.product_id', 'products.id')
                    ->where('pp.currency_code', $currency);

                if ($f['price_gte'] !== null) {
                    $sub->where('pp.amount', '>=', $f['price_gte']);
                }
                if ($f['price_lte'] !== null) {
                    $sub->where('pp.amount', '<=', $f['price_lte']);
                }
            });
        }

        if ($f['in_stock'] === true && count($stockIds) > 0) {
            $query->whereExists(function ($sub) use ($stockIds) {
                $sub->selectRaw('1')
                    ->from('stock_levels as sl')
                    ->whereColumn('sl.product_id', 'products.id')
                    ->whereIn('sl.stock_id', $stockIds)
                    ->where('sl.quantity', '>', 0);
            });
        }
    }

    private function applyAttributeFilter(Builder $query, string $code, array $values): void
    {
        $values = array_values(array_filter($values, static fn ($v) => $v !== null && $v !== ''));
        if (count($values) === 0) {
            return;
        }

        $query->whereExists(function ($sub) use ($code, $values) {
            $sub->selectRaw('1')
                ->from('product_attributes as pa')
                ->join('attributes as a', 'a.id', '=', 'pa.attribute_id')
                ->leftJoin('attribute_options as ao', 'ao.id', '=', 'pa.option_id')
                ->whereColumn('pa.product_id', 'products.id')
                ->where('a.code', $code)
                ->where(function ($q) use ($values) {
                    $q->whereIn('ao.value', $values)
                        ->orWhereIn('pa.value', $values);
                });
        });
    }

    private function applyPublishableFilter(Builder $query, ProductListQuery $q): void
    {
        if ($q->profile !== ProductFilters::PROFILE_ADMIN) {
            return;
        }

        $publishable = $q->productFilters['publishable'] ?? null;
        if ($publishable === null) {
            return;
        }

        $ownerType = addslashes(Product::class);

        if ($publishable) {
            $query->whereExists(function ($sub) use ($ownerType) {
                $sub->selectRaw('1')
                    ->from('media_attachments as ma')
                    ->where('ma.owner_type', $ownerType)
                    ->whereColumn('ma.owner_id', 'products.id')
                    ->where('ma.role', 'thumbnail')
                    ->where('ma.is_primary', true);
            });

            $query->whereExists(function ($sub) use ($ownerType) {
                $sub->selectRaw('1')
                    ->from('media_attachments as ma2')
                    ->where('ma2.owner_type', $ownerType)
                    ->whereColumn('ma2.owner_id', 'products.id')
                    ->where('ma2.role', 'gallery')
                    ->groupBy('ma2.owner_id')
                    ->havingRaw('COUNT(*) >= 7');
            });
        }
    }

    private function applyStorefrontSorting(Builder $query, ProductListQuery $q): void
    {
        $sort = $q->sort;
        $currency = $q->currencyCode;

        if ($sort === 'newest') {
            $query->orderByDesc('products.published_at')->orderByDesc('products.created_at');

            return;
        }

        if ($sort === 'name') {
            $query->orderBy('products.name');

            return;
        }

        if ($sort === '-name') {
            $query->orderByDesc('products.name');

            return;
        }

        if ($sort === 'price' || $sort === '-price') {
            $dir = $sort === 'price' ? 'asc' : 'desc';

            $query->addSelect([
                'sort_price' => DB::table('product_prices as pp')
                    ->select('pp.amount')
                    ->whereColumn('pp.product_id', 'products.id')
                    ->where('pp.currency_code', $currency)
                    ->limit(1),
            ])->orderBy('sort_price', $dir)->orderBy('products.id');

            return;
        }

        // Fallback (should not happen due to normalization)
        $query->orderByDesc('products.published_at')->orderByDesc('products.created_at');
    }

    private function applyAdminSorting(Builder $query, ProductListQuery $q): void
    {
        $sort = $q->sort;

        if ($sort === 'name') {
            $query->orderBy('products.name');

            return;
        }
        if ($sort === '-name') {
            $query->orderByDesc('products.name');

            return;
        }
        if ($sort === 'updated_at') {
            $query->orderBy('products.updated_at');

            return;
        }
        if ($sort === '-updated_at') {
            $query->orderByDesc('products.updated_at');

            return;
        }
        if ($sort === 'active') {
            $query->orderBy('products.active')->orderBy('products.updated_at');

            return;
        }
        if ($sort === '-active') {
            $query->orderByDesc('products.active')->orderByDesc('products.updated_at');

            return;
        }
        if ($sort === 'type') {
            $query->orderBy('products.type')->orderBy('products.updated_at');

            return;
        }
        if ($sort === '-type') {
            $query->orderByDesc('products.type')->orderByDesc('products.updated_at');

            return;
        }

        $query->orderByDesc('products.updated_at');
    }
}
