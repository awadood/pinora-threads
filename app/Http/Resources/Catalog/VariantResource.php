<?php

namespace App\Http\Resources\Catalog;

use App\Http\Resources\Media\MediaAttachmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * VariantResource
 *
 * @author Abdul Wadood
 */
class VariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'default' => $this->default,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'attributes' => VariantAttributeResource::collection($this->whenLoaded('attributes')),

            'product' => ProductResource::make($this->whenLoaded('product')),

            'prices' => VariantPriceResource::collection($this->whenLoaded('prices')),

            'thumbnailMedia' => MediaAttachmentResource::make($this->whenLoaded('thumbnailMedia')),

            'availability' => [
                'in_stock' => $this->in_stock ?? false,
                'country_qty' => $this->country_qty ?? 0,
            ],
        ];
    }
}
