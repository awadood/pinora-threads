<?php

namespace App\Repositories\Catalog;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductVariantRepository;
use App\Repositories\Catalog\Filters\VariantFilters;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function createFor(Product $product, array $data): ProductVariant
    {
        return DB::transaction(function () use ($product, $data) {

            // Lock existing variants of this product to avoid race conditions
            $hasAnyVariant = $this->query()->where('product_id', $product->id)->lockForUpdate()->exists();

            // If first variant => force default
            if (! $hasAnyVariant) {
                $data['default'] = true;
            }

            // If incoming default=true => unset all others first
            if (($data['default'] ?? false) === true) {
                $this->query()->where('product_id', $product->id)->update(['default' => false]);
            }

            $variant = $this->create(Arr::except($data, ['attributes']));

            if (Arr::exists($data, 'attributes') && ! empty($data['attributes'])) {
                $variant->attributes()->createMany(
                    collect($data['attributes'])->map(function ($row) {
                        return [
                            'attribute_id' => $row['attribute_id'],
                            'option_id' => $row['option_id'] ?? null,
                            'value' => $row['value'] ?? null,
                        ];
                    })->all()
                );
            }

            return $variant->load('attributes');
        });
    }

    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variant, $data) {

            // Lock sibling variants to enforce "exactly one default" invariant safely
            ProductVariant::where('product_id', $variant->product_id)->lockForUpdate()->get(['id']);

            $wantsDefault = Arr::exists($data, 'default') && $data['default'] === true;
            $wantsUnsetDefault = Arr::exists($data, 'default') && $data['default'] === false;

            // If setting this variant as default => unset others
            if ($wantsDefault) {
                ProductVariant::where('product_id', $variant->product_id)
                    ->where('id', '!=', $variant->id)
                    ->update(['default' => false]);
            }

            // Prevent removing default if it would leave product without any default
            if ($wantsUnsetDefault && $variant->default) {
                $otherDefaultExists = ProductVariant::where('product_id', $variant->product_id)
                    ->where('id', '!=', $variant->id)
                    ->where('default', true)
                    ->exists();

                if (! $otherDefaultExists) {
                    throw ValidationException::withMessages([
                        'variants' => 'Each product must have at least one default variant.',
                    ]);
                }
            }

            // Optional: if deactivating a default variant, promote another one first
            if (array_key_exists('active', $data) && $data['active'] === false && $variant->default) {
                $replacement = ProductVariant::where('product_id', $variant->product_id)
                    ->where('id', '!=', $variant->id)
                    ->orderBy('id')
                    ->first();

                if (! $replacement) {
                    throw ValidationException::withMessages([
                        'active' => 'Cannot deactivate the only variant of a product.',
                    ]);
                }

                $replacement->update(['default' => true]);
                $variant->default = false;
            }

            $variant->update(Arr::except($data, ['attributes']));

            if (! Arr::exists($data, 'attributes') || empty($data['attributes'])) {
                return $variant->fresh()->load('attributes');
            }

            // Normalize incoming rows
            $rows = collect($data['attributes'])->map(function ($row) use ($variant) {
                return [
                    'product_variant_id' => $variant->id,
                    'attribute_id' => $row['attribute_id'],
                    'option_id' => $row['option_id'] ?? null,
                    'value' => $row['value'] ?? null,
                ];
            });

            // 1) Delete attributes removed from payload
            $incomingAttributeIds = $rows->pluck('attribute_id')->unique()->values()->all();

            $variant->attributes()->whereNotIn('attribute_id', $incomingAttributeIds)->delete();

            ProductVariantAttribute::upsert($rows->all(), ['product_variant_id', 'attribute_id'], ['option_id', 'value']);

            return $variant->fresh()->load('attributes');
        });
    }
}
