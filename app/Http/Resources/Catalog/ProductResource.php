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

            'prices' => ProductPriceResource::collection($this->whenLoaded('prices')),
        ];
    }
}
