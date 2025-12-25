<?php

namespace App\Repositories\Catalog;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * ProductRepository
 *
 * Concrete repository for Product model.
 *
 * @author Abdul Wadood
 */
class ProductRepository extends BaseRepository implements IProductRepository
{
    protected string $modelClass = Product::class;

    public function createWithDefaultVariant(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            /** @var Product $product */
            $product = $this->create($data);

            // Always create the default variant
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $product->sku.'-DEFAULT',
                'title' => $product->name,
                'description' => null,
                'default' => true,
                'active' => false,
            ]);

            return $product->fresh(['variants']);
        });
    }

    public function activate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $product->load(['variants.prices', 'mediaAttachments']);

            // Rule 1: default variant must exist and be active
            $defaultVariant = $product->variants->firstWhere('default', true);

            if (! $defaultVariant) {
                throw ValidationException::withMessages(['variants' => 'Default variant is missing.']);
            }

            if (! $defaultVariant->active) {
                throw ValidationException::withMessages([
                    'variants' => 'Default variant must be active before activating the product.',
                ]);
            }

            // Rule 2: pricing completeness for all ACTIVE variants (USD + PKR)
            $requiredCurrencies = ['USD', 'PKR'];

            foreach ($product->variants->where('active', true) as $variant) {
                $have = $variant->prices->pluck('currency_code')->unique()->values()->all();
                $missing = array_values(array_diff($requiredCurrencies, $have));

                if (count($missing) > 0) {
                    throw ValidationException::withMessages([
                        'pricing' => "Variant {$variant->sku} is missing prices for: ".implode(', ', $missing),
                    ]);
                }
            }

            // Rule 3: required media on product (thumbnail + at least one gallery)
            $attachments = $product->mediaAttachments;

            $hasThumb = $attachments->where('role', 'thumbnail')->where('is_primary', true)->count() === 1;
            $hasGallery = $attachments->where('role', 'gallery')->count() >= 1;

            if (! $hasThumb || ! $hasGallery) {
                throw ValidationException::withMessages([
                    'media' => 'Product requires a primary thumbnail and at least one gallery image before activation.',
                ]);
            }

            // Activate
            $product->active = true;
            $product->save();

            return $product->fresh();
        });
    }

    public function deactivate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $product->update(['active' => false]);

            return $product->fresh();
        });
    }
}
