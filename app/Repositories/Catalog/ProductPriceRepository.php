<?php

namespace App\Repositories\Catalog;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Repositories\BaseRepository;
use App\Repositories\Catalog\Contracts\IProductPriceRepository;
use Illuminate\Support\Facades\DB;

/**
 * ProductPriceRepository
 *
 * Concrete repository for ProductPrice model.
 *
 * @author Abdul Wadood
 */
class ProductPriceRepository extends BaseRepository implements IProductPriceRepository
{
    protected string $modelClass = ProductPrice::class;

    public function savePrices(Product $product, array $payload): array
    {
        return DB::transaction(function () use ($product, $payload) {
            $productUpserted = 0;
            // 1) Product prices: upsert by (product_id, currency_code)
            $productPrices = $payload['product_prices'] ?? [];
            if (is_array($productPrices) && count($productPrices) > 0) {
                $rows = [];
                foreach ($productPrices as $p) {
                    $rows[] = [
                        'product_id' => $product->id,
                        'currency_code' => $p['currency_code'],
                        'amount' => $p['amount'],
                        'compare_at' => $p['compare_at'] ?? null,
                    ];
                }

                ProductPrice::upsert(
                    $rows,
                    ['product_id', 'currency_code'],
                    ['amount', 'compare_at']
                );

                $productUpserted = count($rows);
            }

            return [
                'product_upserted' => $productUpserted,
            ];
        });
    }
}
