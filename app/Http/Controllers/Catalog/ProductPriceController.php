<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\PriceRequest;
use App\Http\Requests\Catalog\ProductPriceRequest;
use App\Http\Resources\Catalog\ProductPriceResource;
use App\Models\Product;
use App\Repositories\Catalog\Contracts\IProductPriceRepository;
use Illuminate\Routing\Controller;

/**
 * ProductPriceController
 *
 * Manage product-level prices per currency.
 *
 * @author Abdul Wadood
 */
class ProductPriceController extends Controller
{
    public function __construct(protected IProductPriceRepository $prices) {}

    public function save(PriceRequest $request, Product $product)
    {
        $data = $request->validated();

        $result = $this->prices->savePrices($product, $data);

        return response()->json($result, 201);
    }

    public function store(ProductPriceRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['product_id'] = $product->id;

        $price = $this->prices->create($data);

        return ProductPriceResource::make($price)->response()->setStatusCode(201);
    }

    public function update(ProductPriceRequest $request, Product $product, string $currency_code)
    {
        $price = $this->prices->query()
            ->where('product_id', $product->id)
            ->where('currency_code', $currency_code)
            ->firstOrFail();

        $price->fill($request->validated())->save();

        return ProductPriceResource::make($price);
    }

    public function destroy(Product $product, string $currency_code)
    {
        $price = $this->prices->query()
            ->where('product_id', $product->id)
            ->where('currency_code', $currency_code)
            ->firstOrFail();

        $this->prices->destroy([$product->id, $currency_code]);

        return response()->json([], 204);
    }
}
