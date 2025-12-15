<?php

namespace App\Repositories\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;

class VariantFilters
{
    public function apply(Builder $query, array $filters): Builder
    {
        if (! is_array($filters)) {
            return $query;
        }

        // Base-table filters that are safe
        if (isset($filters['active.eq'])) {
            $query->where('active', (bool) $filters['active.eq']);
        }

        // Special "q" filter for lookup/search box
        $q = $filters['q.like'] ?? null;
        $q = is_string($q) ? trim($q) : null;

        if ($q !== null && $q !== '') {
            $query->where(function (Builder $w) use ($q) {
                $like = "%{$q}%";

                // product_variants columns
                $w->where('sku', 'ilike', $like)
                    ->orWhere('title', 'ilike', $like)
                    ->orWhere('description', 'ilike', $like);

                // products columns
                $w->orWhereHas('product', function (Builder $p) use ($like) {
                    $p->where('name', 'ilike', $like)
                        ->orWhere('slug', 'ilike', $like);
                });

                // product_variant_attributes.value (free-text attribute values)
                $w->orWhereHas('attributes', function (Builder $a) use ($like) {
                    $a->whereNotNull('value')
                        ->where('value', 'ilike', $like);
                });

                // attribute_options.value (select attributes)
                $w->orWhereHas('attributes.option', function (Builder $o) use ($like) {
                    $o->where('value', 'ilike', $like);
                });
            });
        }

        return $query;
    }
}
