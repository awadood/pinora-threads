<?php

namespace App\Repositories\Catalog;

use App\Models\Product;
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

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            /** @var Product $product */
            $product = $this->create($data);

            return $product->fresh();
        });
    }

    public function activate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $product->load(['prices', 'media']);

            // Rule 1: pricing completeness (USD + PKR)
            $requiredCurrencies = ['USD', 'PKR'];

            $have = $product->prices->pluck('currency_code')->unique()->values()->all();
            $missing = array_values(array_diff($requiredCurrencies, $have));
            if (count($missing) > 0) {
                throw ValidationException::withMessages([
                    'pricing' => 'Product is missing prices for: '.implode(', ', $missing),
                ]);
            }

            // Rule 2: required media on product (thumbnail + at least one gallery)
            $attachments = $product->media;

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
