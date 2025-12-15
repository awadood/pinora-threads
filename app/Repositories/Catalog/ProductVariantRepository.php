<?php

namespace App\Repositories\Catalog;

use App\Models\ProductVariant;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductVariantRepository;
use App\Repositories\Catalog\Filters\VariantFilters;
use Illuminate\Database\Eloquent\Collection;

/**
 * ProductVariantRepository
 *
 * Concrete repository for ProductVariant model.
 *
 * @author Abdul Wadood
 */
class ProductVariantRepository extends BaseRepository implements IProductVariantRepository
{
    protected string $modelClass = ProductVariant::class;

    public function __construct(private readonly VariantFilters $variantFilters) {}

    public function lookup(array $filters, array $with = []): Collection
    {
        $query = $this->query()->with($with);

        // Apply domain-aware filters (q searches across relationships)
        $this->variantFilters->apply($query, $filters);

        return $query
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }
}
