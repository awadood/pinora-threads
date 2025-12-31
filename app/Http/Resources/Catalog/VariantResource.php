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
            'title' => $this->title,
            'description' => $this->description,
            'default' => $this->default,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'attributes' => VariantAttributeResource::collection($this->whenLoaded('attributes')),

            'product' => ProductResource::make($this->whenLoaded('product')),

            'prices' => VariantPriceResource::collection($this->whenLoaded('prices')),

            'media' => MediaAttachmentResource::collection($this->whenLoaded('media')),
        ];
    }
}
