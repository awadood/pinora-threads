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
}
