<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductVariantPriceRequest;
use App\Http\Resources\Catalog\ProductVariantPriceResource;
use App\Models\ProductVariant;
use App\Repositories\Catalog\Contracts\IProductVariantPriceRepository;
use Illuminate\Routing\Controller;

/**
 * ProductVariantPriceController
 *
 * Manage variant-level prices per currency.
 *
 * @author Abdul Wadood
 */
class ProductVariantPriceController extends Controller
{
    public function __construct(protected IProductVariantPriceRepository $prices){}

    public function indexByVariant(ProductVariant $id)
    {
        $prices = $this->prices->query()->where('product_variant_id', $id->id)->get();

        return ProductVariantPriceResource::collection($prices);
    }

    public function store(ProductVariantPriceRequest $request, ProductVariant $variant)
    {
        $data = $request->validated();
        $data['product_variant_id'] = $variant->id;

        $price = $this->prices->create($data);

        return (ProductVariantPriceResource::make($price))->response()->setStatusCode(201);
    }

    public function update(ProductVariantPriceRequest $request, ProductVariant $variant, string $currency_code)
    {
        $price = $this->prices->query()
            ->where('product_variant_id', $variant->id)
            ->where('currency_code', $currency_code)
            ->firstOrFail();

        $price->fill($request->validated())->save();

        return ProductVariantPriceResource::make($price);
    }

    public function destroy(ProductVariant $variant, string $currency_code)
    {
        $price = $this->prices->query()
            ->where('product_variant_id', $variant->id)
            ->where('currency_code', $currency_code)
            ->firstOrFail();

        $this->prices->destroy([$variant->id, $currency_code]);

        return response()->json([], 204);
    }
}
