<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\ProductVariantPriceRequest;
use App\Http\Resources\Catalog\VariantPriceResource;
use App\Models\ProductVariant;
use App\Repositories\Catalog\Contracts\IProductVariantPriceRepository;
use Illuminate\Routing\Controller;

/**
 * VariantPriceController
 *
 * Manage variant-level prices per currency.
 *
 * @author Abdul Wadood
 */
class VariantPriceController extends Controller
{
    public function __construct(protected IProductVariantPriceRepository $prices) {}

    public function indexByVariant(ProductVariant $id)
    {
        $prices = $this->prices->query()->where('product_variant_id', $id->id)->get();

        return VariantPriceResource::collection($prices);
    }

    public function store(ProductVariantPriceRequest $request, ProductVariant $variant)
    {
        $data = $request->validated();
        $data['product_variant_id'] = $variant->id;

        $price = $this->prices->create($data);

        return VariantPriceResource::make($price)->response()->setStatusCode(201);
    }

    public function update(ProductVariantPriceRequest $request, ProductVariant $variant, string $currency_code)
    {
        $price = $this->prices->query()
            ->where('product_variant_id', $variant->id)
            ->where('currency_code', $currency_code)
            ->firstOrFail();

        $price->fill($request->validated())->save();

        return VariantPriceResource::make($price);
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
