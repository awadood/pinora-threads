<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProductResource
 *
 * @author Abdul Wadood
 */
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description,
            'tax_class_id' => $this->tax_class_id,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'variants' => VariantResource::collection($this->whenLoaded('variants')),

            'bundles' => ProductBundleResource::collection($this->whenLoaded('bundles')),

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),

            'sort' => $this->whenPivotLoaded('collection_product', function () {
                return $this->pivot->sort;
            }),

            // storefront displays thumbnail of variant selected by input query params.
            'selected_variant' => VariantResource::make($this->whenLoaded('selectedVariant')),

            'variants_count' => $this->variants_count ?? 0,
        ];
    }
}
